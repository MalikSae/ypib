<?php

namespace App\Livewire\Admin;

use App\Models\ExamQuestion;
use App\Livewire\Concerns\AuthorizesAccess;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Bank soal. Cari pertanyaan langsung menyaring dan tersimpan di URL. */
class ExamQuestionList extends Component
{
    use AuthorizesAccess, WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public function boot(): void
    {
        $this->authorizeGlobalStaff();
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
