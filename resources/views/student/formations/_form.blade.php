{{-- File: resources/views/student/formations/_form.blade.php --}}
{{-- Shared form fields for student formation create/edit. --}}
@php
    $formation = $formation ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nom <span class="text-rose-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $formation?->name) }}" required
               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('name') border-rose-400 @enderror">
        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('description', $formation?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="level" class="block text-sm font-medium text-slate-700 mb-1.5">Niveau <span class="text-rose-500">*</span></label>
            <select id="level" name="level" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                @foreach (['débutant', 'intermédiaire', 'avancé'] as $lvl)
                    <option value="{{ $lvl }}" @selected(old('level', $formation?->level) === $lvl)>{{ ucfirst($lvl) }}</option>
                @endforeach
            </select>
            @error('level')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="duration_hours" class="block text-sm font-medium text-slate-700 mb-1.5">Durée (heures)</label>
            <input type="number" id="duration_hours" name="duration_hours" min="1" max="9999"
                   value="{{ old('duration_hours', $formation?->duration_hours) }}"
                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            @error('duration_hours')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700 mb-1.5">Statut <span class="text-rose-500">*</span></label>
            <select id="status" name="status" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="draft"     @selected(old('status', $formation?->status) === 'draft')>Brouillon</option>
                <option value="published" @selected(old('status', $formation?->status) === 'published')>Publié</option>
            </select>
            @error('status')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
