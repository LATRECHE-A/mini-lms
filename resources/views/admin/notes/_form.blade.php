<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="user_id" class="block text-sm font-medium text-slate-700 mb-1.5">Apprenant *</label>
        <select id="user_id" name="user_id" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('user_id') border-rose-400 @enderror">
            <option value="">Choisir...</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}" {{ old('user_id', $note->user_id ?? '') == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
            @endforeach
        </select>
        @error('user_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="formation_id" class="block text-sm font-medium text-slate-700 mb-1.5">Formation *</label>
        <select id="formation_id" name="formation_id" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <option value="">Choisir...</option>
            @foreach($formations as $formation)
                <option value="{{ $formation->id }}" {{ old('formation_id', $note->formation_id ?? '') == $formation->id ? 'selected' : '' }}>{{ $formation->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="subject" class="block text-sm font-medium text-slate-700 mb-1.5">Matière / Module *</label>
        <input type="text" id="subject" name="subject" value="{{ old('subject', $note->subject ?? '') }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
    </div>
    <div>
        <label for="grade" class="block text-sm font-medium text-slate-700 mb-1.5">Note / 20 *</label>
        <input type="number" id="grade" name="grade" value="{{ old('grade', $note->grade ?? '') }}" min="0" max="20" step="0.5" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        @error('grade')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>
<div>
    <label for="comment" class="block text-sm font-medium text-slate-700 mb-1.5">Commentaire</label>
    <textarea id="comment" name="comment" rows="2" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('comment', $note->comment ?? '') }}</textarea>
</div>
