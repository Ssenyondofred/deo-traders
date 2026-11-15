@extends('layouts.admin')

@section('title', 'Edit Brand')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Edit Brand</h1>
            <a href="{{ route('brands.index') }}" class="text-sm text-blue-600 hover:underline">← Back</a>
        </div>

        <form action="{{ route('brands.update', $brand) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name *</label>
                <input type="text" name="name" value="{{ old('name', $brand->name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description', $brand->description) }}</textarea>
            </div>

            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $brand->is_active) ? 'checked' : '' }}
                           class="rounded">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" 
                        style="background-color: #2563eb; color: white; padding: 10px 24px; border-radius: 8px; border: none; font-weight: 500; cursor: pointer;">
                    Update Brand
                </button>

                <a href="{{ route('brands.index') }}" 
                   style="background-color: #e5e7eb; color: #374151; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection