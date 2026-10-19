<?php

namespace Tests\Feature\Pmb;

use App\Models\Campus;
use App\Models\PeriodProgram;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/** Nominal rupiah: tampil dengan titik ribuan (1.500.000), di tampilan maupun di kolom isian. */
class MoneyFormatTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    // ── Penjaga statis ─────────────────────────────────────────────────────

    /** Semua file view dan app yang dipindai untuk pola number_format. */
    private function sourceFiles(): array
    {
        $files = [];
        foreach ([base_path('resources/views'), base_path('app')] as $dir) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /** Ambil argumen tingkat atas dari setiap pemanggilan number_format(...) dalam teks. */
    private function numberFormatCalls(string $source): array
    {
        $calls = [];
        $offset = 0;

        while (($start = strpos($source, 'number_format(', $offset)) !== false) {
            $i = $start + strlen('number_format(');
            $depth = 1;
            $args = [];
            $current = '';
            $quote = null;

            for ($n = strlen($source); $i < $n && $depth > 0; $i++) {
                $ch = $source[$i];

                if ($quote) {
                    $current .= $ch;
                    if ($ch === $quote && $source[$i - 1] !== '\\') {
                        $quote = null;
                    }
                    continue;
                }

                if ($ch === "'" || $ch === '"') {
                    $quote = $ch;
                    $current .= $ch;
                } elseif ($ch === '(' || $ch === '[') {
                    $depth++;
                    $current .= $ch;
                } elseif ($ch === ')' || $ch === ']') {
                    $depth--;
                    if ($depth > 0) {
                        $current .= $ch;
                    }
                } elseif ($ch === ',' && $depth === 1) {
                    $args[] = trim($current);
                    $current = '';
                } else {
                    $current .= $ch;
                }
            }

            $args[] = trim($current);
            $calls[] = ['args' => $args, 'line' => substr_count(substr($source, 0, $start), "\n") + 1];
            $offset = $start + 1;
        }

        return $calls;
    }

    public function test_every_number_format_uses_dot_thousands_and_comma_decimals(): void
    {
        $violations = [];

        foreach ($this->sourceFiles() as $path) {
            foreach ($this->numberFormatCalls(file_get_contents($path)) as $call) {
                $args = array_map(fn ($a) => str_replace(['"', ' '], ["'", ''], $a), $call['args']);

                // number_format($x, <desimal>, ',', '.')
                $ok = count($args) === 4 && $args[2] === "','" && $args[3] === "'.'";

                if (!$ok) {
                    $violations[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path) . ':' . $call['line']
                        . ' number_format(' . implode(', ', $call['args']) . ')';
                }
            }
        }

        $this->assertSame([], $violations, "number_format harus memakai ', ' dan '.' (titik ribuan):\n" . implode("\n", $violations));
    }

    public function test_no_money_field_is_a_plain_number_input(): void
    {
        $violations = [];

        foreach ($this->sourceFiles() as $path) {
            foreach (file($path) as $index => $line) {
                // payment_grace_days adalah jumlah hari, bukan nominal.
                $scan = str_replace('payment_grace_days', '', $line);

                if (preg_match('/type="number"/', $scan) && preg_match('/(fee|amount|reward|payment|biaya|nominal|harga|price)/i', $scan)) {
                    $violations[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path) . ':' . ($index + 1);
                }
            }
        }

        $this->assertSame([], $violations, "Kolom nominal harus memakai <x-money-input>:\n" . implode("\n", $violations));
    }

    // ── Kolom isian ────────────────────────────────────────────────────────

    private function programWithTerms(): array
    {
        $period = $this->period();
        $program = $this->program();
        PeriodProgram::where('program_id', $program->id)->delete();
        PeriodProgram::create([
            'period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => Campus::defaultId(),
            'is_offered' => true, 'quota' => 100, 'registration_fee' => 1500000,
            're_registration_minimum_payment' => 4000000, 'referral_reward_amount' => 50000,
            're_registration_reward_amount' => 1123000,
            're_registration_fee_details' => [['name' => 'Uang Pangkal', 'amount' => 5000000]],
            're_registration_fee' => 5000000,
        ]);

        return [$period, $program];
    }

    public function test_pricing_page_shows_money_fields_with_thousand_dots_and_posts_plain_digits(): void
    {
        [$period, $program] = $this->programWithTerms();

        $html = $this->actingAs($this->makeUser('admin'))->get("/admin/periode/{$period->id}/biaya")->assertOk()->getContent();

        // Tampilan: titik ribuan pada kolom yang terlihat.
        // Rincian biaya daftar ulang dirender Alpine (x-for), jadi diverifikasi di browser, bukan di HTML server.
        foreach (['1.500.000', '4.000.000', '50.000', '1.123.000'] as $shown) {
            $this->assertStringContainsString('value="' . $shown . '"', $html, "kolom harus menampilkan {$shown}");
        }

        // Yang dikirim ke server: angka polos lewat input tersembunyi.
        foreach ([
            'registration_fee' => '1500000', 're_registration_minimum_payment' => '4000000',
            'referral_reward_amount' => '50000', 're_registration_reward_amount' => '1123000',
        ] as $field => $raw) {
            $this->assertMatchesRegularExpression(
                '/<input type="hidden" name="programs\[' . $program->id . '\]\[' . $field . '\]" value="' . $raw . '"/',
                $html,
                "{$field} harus dikirim sebagai {$raw}"
            );
        }

        // Kuota bukan nominal: tetap angka biasa.
        $this->assertMatchesRegularExpression('/type="number"[^>]*name="programs\[' . $program->id . '\]\[quota\]"/', $html);
    }

    public function test_pricing_details_accept_amounts_typed_with_dots(): void
    {
        [$period, $program] = $this->programWithTerms();

        $this->actingAs($this->makeUser('admin'))->put("/admin/periode/{$period->id}/biaya", [
            'campus_id' => Campus::defaultId(),
            'programs' => [$program->id => [
                'is_offered' => 1, 'quota' => 100, 'registration_fee' => '2500000',
                're_registration_minimum_payment' => '3000000', 'referral_reward_amount' => '75000',
                're_registration_reward_amount' => '1250000',
                'fee_names' => ['Uang Pangkal', 'SPP Semester 1'],
                'fee_amounts' => ['6.500.000', '1.500.000'],
            ]],
        ])->assertSessionHasNoErrors();

        $row = PeriodProgram::where('program_id', $program->id)->where('campus_id', Campus::defaultId())->first();
        $this->assertSame(8000000, (int) $row->re_registration_fee);
        $this->assertSame(2500000, (int) $row->registration_fee);
        $this->assertSame(6500000, (int) $row->re_registration_fee_details[0]['amount']);
    }

    public function test_program_form_campus_fee_uses_the_money_input(): void
    {
        [, $program] = $this->programWithTerms();

        $html = $this->actingAs($this->makeUser('admin'))->get("/admin/programs/{$program->id}/edit")->assertOk()->getContent();

        $campusId = Campus::defaultId();
        $this->assertStringContainsString('value="1.500.000"', $html);
        $this->assertMatchesRegularExpression(
            '/<input type="hidden" name="offers\[' . $campusId . '\]\[fee\]" value="1500000"/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression('/type="number"[^>]*name="offers\[\d+\]\[fee\]"/', $html);
    }
}
