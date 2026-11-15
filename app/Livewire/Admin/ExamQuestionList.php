<?php

namespace App\Livewire\Admin;

use App\Models\ExamQuestion;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Bank soal. Cari pertanyaan langsung menyaring dan tersimpan di URL. */
class ExamQuestionList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    /** Sama dengan middleware global.admin: admin atau operator tanpa batasan kampus. */
    public function boot(): void
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['admin', 'operator'], true) && ! $user->campus_id, 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = trim($this->search);

        return view('livewire.admin.exam-question-list', [
            'questions' => ExamQuestion::query()
                ->when($term !== '', fn ($q) => $q->where('question_text', 'like', "%{$term}%"))
                ->latest()
                ->paginate(10),
        ]);
    }
}
