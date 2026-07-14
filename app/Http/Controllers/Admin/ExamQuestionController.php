<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;

class ExamQuestionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $questions = ExamQuestion::query()
            ->when($search, function ($query, $search) {
                $query->where('question_text', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.exam-questions.index', compact('questions', 'search'));
    }

    public function create()
    {
        return view('admin.exam-questions.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string|max:500',
            'option_b' => 'required|string|max:500',
            'option_c' => 'required|string|max:500',
            'option_d' => 'required|string|max:500',
            'correct_option' => 'required|in:a,b,c,d',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        ExamQuestion::create($validated);

        return redirect()->route('admin.bank-soal.index')->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(ExamQuestion $bank_soal)
    {
        // the route parameter is bank_soal because of the resource name 'bank-soal'
        return view('admin.exam-questions.form', ['question' => $bank_soal]);
    }

    public function update(Request $request, ExamQuestion $bank_soal)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string|max:500',
            'option_b' => 'required|string|max:500',
            'option_c' => 'required|string|max:500',
            'option_d' => 'required|string|max:500',
            'correct_option' => 'required|in:a,b,c,d',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $bank_soal->update($validated);

        return redirect()->route('admin.bank-soal.index')->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(ExamQuestion $bank_soal)
    {
        $bank_soal->delete();
        return redirect()->route('admin.bank-soal.index')->with('success', 'Soal berhasil dihapus.');
    }
}
