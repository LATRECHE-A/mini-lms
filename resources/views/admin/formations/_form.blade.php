<div>
    <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nom de la formation *</label>
    <input type="text" id="name" name="name" value="{{ old('name', $formation->name ?? '') }}" required
        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('name') border-rose-400 @enderror">
    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
    <textarea id="description" name="description" rows="3"
        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('description') border-rose-400 @enderror">{{ old('description', $formation->description ?? '') }}</textarea>
    @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <label for="level" class="block text-sm font-medium text-slate-700 mb-1.5">Niveau *</label>
        <select id="level" name="level" required
            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            @foreach(['débutant', 'intermédiaire', 'avancé'] as $level)
                <option value="{{ $level }}" {{ old('level', $formation->level ?? '') === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="duration_hours" class="block text-sm font-medium text-slate-700 mb-1.5">Durée (heures)</label>
        <input type="number" id="duration_hours" name="duration_hours" min="1" value="{{ old('duration_hours', $formation->duration_hours ?? '') }}"
            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-slate-700 mb-1.5">Statut *</label>
        <select id="status" name="status" required
            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <option value="draft" {{ old('status', $formation->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Brouillon</option>
            <option value="published" {{ old('status', $formation->status ?? '') === 'published' ? 'selected' : '' }}>Publié</option>
        </select>
    </div>
</div>
