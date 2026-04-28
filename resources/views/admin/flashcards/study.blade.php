{{-- File: resources/views/admin/flashcards/study.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
    .card-flip { perspective: 1000px; }
    .card-inner { transition: transform 0.5s; transform-style: preserve-3d; }
    .card-inner.flipped { transform: rotateY(180deg); }
    .card-face { backface-visibility: hidden; }
    .card-back { transform: rotateY(180deg); }
</style>

<div class="fade-in max-w-2xl mx-auto" x-data="flashcardStudy()">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.flashcards.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Retour
        </a>
        <span class="text-sm text-slate-400" x-text="(Math.min(currentIndex + 1, cards.length)) + ' / ' + cards.length"></span>
    </div>

    <div class="w-full bg-slate-200 rounded-full h-1.5 mb-6">
        <div class="bg-brand-600 h-1.5 rounded-full transition-all duration-300" :style="'width:' + ((currentIndex / cards.length) * 100) + '%'"></div>
    </div>

    <div x-show="!done" class="card-flip" style="min-height: 320px;">
        <div class="card-inner relative w-full" :class="flipped ? 'flipped' : ''" style="min-height: 320px;">
            <div class="card-face absolute inset-0 bg-white rounded-2xl border border-slate-200 shadow-sm p-8 flex flex-col items-center justify-center cursor-pointer" @click="flip()">
                <p class="text-xs text-slate-400 mb-4 uppercase tracking-wide">Question</p>
                <p class="text-lg text-slate-900 text-center font-medium leading-relaxed" x-text="currentCard?.question"></p>
                <p class="text-xs text-slate-400 mt-6">Cliquez pour voir la réponse</p>
            </div>
            <div class="card-face card-back absolute inset-0 bg-gradient-to-br from-brand-50 to-sky-50 rounded-2xl border border-brand-200 shadow-sm p-8 flex flex-col items-center justify-center">
                <p class="text-xs text-brand-500 mb-4 uppercase tracking-wide">Réponse</p>
                <p class="text-base text-slate-800 text-center leading-relaxed" x-text="currentCard?.answer"></p>
            </div>
        </div>
    </div>

    <div x-show="flipped && !done" x-transition class="mt-6 space-y-3">
        <p class="text-xs text-center text-slate-400">Comment avez-vous répondu ?</p>
        <div class="grid grid-cols-3 gap-3">
            <button type="button" @click="rate(1)" :disabled="loading" class="py-3 rounded-xl border-2 border-rose-200 bg-rose-50 text-rose-700 text-sm font-medium hover:bg-rose-100 transition-colors disabled:opacity-60">😓 Difficile</button>
            <button type="button" @click="rate(3)" :disabled="loading" class="py-3 rounded-xl border-2 border-amber-200 bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100 transition-colors disabled:opacity-60">🤔 Moyen</button>
            <button type="button" @click="rate(5)" :disabled="loading" class="py-3 rounded-xl border-2 border-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition-colors disabled:opacity-60">😊 Facile</button>
        </div>
    </div>

    <div x-show="done" x-transition class="mt-8 text-center bg-white rounded-2xl border border-slate-200 p-8">
        <div class="text-4xl mb-3">🎉</div>
        <h2 class="text-xl font-bold text-slate-900 mb-2">Session terminée !</h2>
        <p class="text-sm text-slate-500 mb-4">Vous avez révisé <span class="font-semibold" x-text="cards.length"></span> carte(s).</p>
        <a href="{{ route('admin.flashcards.index') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors inline-block">Retour</a>
    </div>
</div>

<script>
function flashcardStudy() {
    return {
        cards: @json($dueCards),
        currentIndex: 0,
        flipped: false,
        done: false,
        loading: false,
        get currentCard() { return this.cards[this.currentIndex] || null; },
        flip() { if (!this.done) this.flipped = true; },
        async rate(quality) {
            if (this.loading || !this.currentCard) return;
            this.loading = true;
            try {
                await fetch(`{{ url('admin/flashcards') }}/${this.currentCard.id}/review`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quality }),
                });
            } catch (e) { /* swallow — we still advance */ }
            this.loading = false;
            this.flipped = false;
            if (this.currentIndex < this.cards.length - 1) {
                this.$nextTick(() => this.currentIndex++);
            } else {
                this.done = true;
            }
        },
    };
}
</script>
@endsection
