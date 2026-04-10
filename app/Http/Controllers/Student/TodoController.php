<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Controller for managing student to-do list.
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\TodoRequest;
use App\Models\Todo;

class TodoController extends Controller
{
    public function index()
    {
        $todos = auth()->user()->todos()
            ->orderBy('is_completed')
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->get();

        return view('student.todos.index', compact('todos'));
    }

    public function store(TodoRequest $request)
    {
        auth()->user()->todos()->create($request->validated());

        return back()->with('success', 'Tâche ajoutée.');
    }

    public function toggle(Todo $todo)
    {
        if ($todo->user_id !== auth()->id()) {
            abort(403);
        }

        $todo->update(['is_completed' => !$todo->is_completed]);

        return back();
    }

    public function destroy(Todo $todo)
    {
        if ($todo->user_id !== auth()->id()) {
            abort(403);
        }

        $todo->delete();

        return back()->with('success', 'Tâche supprimée.');
    }
}
