@extends('layouts.admin')

@section('title', 'Edit Unit')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Edit Unit</h1>
            <a href="{{ route('units.index') }}" class="text-sm text-blue-600 hover:underline">← Back</a>
        </div>

        <form action="{{ route('units.update', $unit) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Name *</label>
                <input type="text" name="name" value="{{ old('name', $unit->name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Short Name *</label>
                <input type="text" name="short_name" value="{{ old('short_name', $unit->short_name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @error('short_name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $unit->is_active) ? 'checked' : '' }}
                           class="rounded">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" 
                        style="background-color: #2563eb; color: white; padding: 10px 24px; border-radius: 8px; border: none; font-weight: 500; cursor: pointer;">
                    Update Unit
                </button>

                <a href="{{ route('units.index') }}" 
                   style="background-color: #e5e7eb; color: #374151; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection