@extends('layouts.app')

@section('content')
<div class="fade-in max-w-2xl">
    <div class="mb-8">
        <a href="{{ route('admin.quizzes.show', $quiz) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>{{ $quiz->title }}</a>
        <h1 class="text-2xl font-bold text-slate-900">Nouvelle question</h1>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6" x-data="questionForm()">
        <form method="POST" action="{{ route('admin.questions.store', $quiz) }}" class="space-y-5">
            @csrf
            <div>
                <label for="question_text" class="block text-sm font-medium text-slate-700 mb-1.5">Question *</label>
                <textarea id="question_text" name="question_text" rows="2" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('question_text') }}</textarea>
                @error('question_text')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-3">Réponses *</label>
                <div class="space-y-3">
                    <template x-for="(answer, index) in answers" :key="index">
                        <div class="flex items-center gap-3">
                            <input type="radio" :name="'correct_answer'" :value="index" x-model="correctAnswer"
                                class="text-brand-600 focus:ring-brand-500" :checked="index === 0">
                            <input type="text" :name="'answers['+index+'][text]'" x-model="answer.text" required
                                placeholder="Texte de la réponse..."
                                class="flex-1 px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <button type="button" @click="removeAnswer(index)" x-show="answers.length > 2"
                                class="text-slate-400 hover:text-rose-600 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <input type="hidden" name="correct_answer" :value="correctAnswer">
                <p class="mt-2 text-xs text-slate-400">Sélectionnez le bouton radio pour indiquer la bonne réponse.</p>
                @error('answers')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                @error('correct_answer')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <button type="button" @click="addAnswer()" x-show="answers.length < 6"
                class="text-sm text-brand-600 hover:text-brand-700 font-medium inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Ajouter une réponse
            </button>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Ajouter la question</button>
                <a href="{{ route('admin.quizzes.show', $quiz) }}" class="text-sm text-slate-500">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function questionForm() {
    return {
        answers: [{ text: '' }, { text: '' }, { text: '' }],
        correctAnswer: 0,
        addAnswer() { if (this.answers.length < 6) this.answers.push({ text: '' }); },
        removeAnswer(index) {
            if (this.answers.length > 2) {
                this.answers.splice(index, 1);
                if (this.correctAnswer >= this.answers.length) this.correctAnswer = 0;
            }
        }
    }
}
</script>
@endsection
