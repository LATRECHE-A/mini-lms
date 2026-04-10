@extends('layouts.app')

@section('content')
<div class="fade-in max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('student.formations.show', $quiz->subChapter->chapter->formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $quiz->subChapter->chapter->formation->name }}
        </a>
        <h1 class="text-2xl font-bold text-slate-900">{{ $quiz->title }}</h1>
        @if($quiz->description)
            <p class="text-sm text-slate-500 mt-1">{{ $quiz->description }}</p>
        @endif
    </div>

    @if($questions->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
            <p class="text-slate-400">Ce quiz ne contient pas encore de questions.</p>
        </div>
    @else

    <div x-data="quizApp()" class="space-y-6">
        {{-- Progress bar --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="flex items-center justify-between text-sm text-slate-600 mb-2">
                <span>Question <span x-text="currentStep + 1"></span> / {{ $questions->count() }}</span>
                <span x-text="answeredCount + ' répondue(s)'" class="text-xs text-slate-400"></span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2">
                <div class="bg-brand-600 h-2 rounded-full transition-all duration-500" :style="'width: ' + ((currentStep + 1) / {{ $questions->count() }} * 100) + '%'"></div>
            </div>
        </div>

        {{-- Question Cards --}}
        <form method="POST" action="{{ route('student.quizzes.submit', $quiz) }}" id="quizForm">
            @csrf
            <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">

            @foreach($questions as $i => $question)
            <div x-show="currentStep === {{ $i }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="flex items-start gap-4 mb-6">
                    <span class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600 font-semibold text-sm flex-shrink-0">{{ $i + 1 }}</span>
                    <p class="text-lg font-medium text-slate-900">{{ $question->question_text }}</p>
                </div>

                <div class="space-y-3 ml-12">
                    @foreach($question->answers as $answer)
                    <label class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all"
                        :class="answers[{{ $question->id }}] == {{ $answer->id }} ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}"
                            x-model="answers[{{ $question->id }}]"
                            class="text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-slate-700">{{ $answer->answer_text }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Navigation --}}
            <div class="flex items-center justify-between mt-6">
                <button type="button" @click="prevStep()" x-show="currentStep > 0"
                    class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg px-4 py-2.5 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Précédent
                </button>
                <div x-show="currentStep === 0"></div>

                <button type="button" @click="nextStep()" x-show="currentStep < {{ $questions->count() - 1 }}"
                    class="inline-flex items-center gap-1.5 text-sm text-white bg-brand-600 hover:bg-brand-700 rounded-lg px-4 py-2.5 transition-colors">
                    Suivant
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <button type="submit" x-show="currentStep === {{ $questions->count() - 1 }}" @click="return confirmSubmit($event)"
                    class="inline-flex items-center gap-1.5 text-sm text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg px-6 py-2.5 transition-colors font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Terminer le quiz
                </button>
            </div>
        </form>

        {{-- Question dots navigation --}}
        <div class="flex flex-wrap items-center justify-center gap-2 pt-4">
            @foreach($questions as $i => $question)
            <button type="button" @click="currentStep = {{ $i }}"
                class="w-8 h-8 rounded-full text-xs font-medium transition-all"
                :class="currentStep === {{ $i }} ? 'bg-brand-600 text-white' : (answers[{{ $question->id }}] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500 hover:bg-slate-200')">
                {{ $i + 1 }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Previous attempts --}}
    @if($history->count())
    <div class="mt-8 bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Tentatives précédentes</h2>
        </div>
        @foreach($history as $past)
        <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
            <span class="text-sm text-slate-600">{{ $past->completed_at->format('d/m/Y H:i') }}</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $past->grade_color }}-100 text-{{ $past->grade_color }}-800">
                {{ $past->score }}/{{ $past->total_questions }} ({{ $past->percentage }}%)
            </span>
        </div>
        @endforeach
    </div>
    @endif

    @endif
</div>

<script>
function quizApp() {
    return {
        currentStep: 0,
        answers: {},
        get answeredCount() {
            return Object.keys(this.answers).filter(k => this.answers[k]).length;
        },
        nextStep() {
            if (this.currentStep < {{ $questions->count() - 1 }}) this.currentStep++;
        },
        prevStep() {
            if (this.currentStep > 0) this.currentStep--;
        },
        confirmSubmit(event) {
            const total = {{ $questions->count() }};
            const answered = this.answeredCount;
            if (answered < total) {
                if (!confirm(`Vous avez répondu à ${answered}/${total} questions. Soumettre quand même ?`)) {
                    event.preventDefault();
                    return false;
                }
            }
            return true;
        }
    }
}
</script>
@endsection
