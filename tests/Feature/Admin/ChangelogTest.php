<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangelogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_changelog_with_latest_release_first(): void
    {
        $releases = require resource_path('data/changelog.php');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.changelog'))
            ->assertOk()
            ->assertSee('Changelog')
            ->assertSee($releases[0]['title'])
            ->assertSee(array_key_first($releases[0]['sections']));
    }

    public function test_non_staff_cannot_view_changelog(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($student)->get(route('admin.changelog'))->assertForbidden();
    }
}
