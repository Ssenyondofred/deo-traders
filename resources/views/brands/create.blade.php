@extends('layouts.admin')

@section('title', 'Add Brand')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Add New Brand</h1>
            <a href="{{ route('brands.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Back
            </a>
        </div>

        <form action="{{ route('brands.store') }}" method="POST">
            @csrf

            <div class="space-y-5">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>

                {{-- Active --}}
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="rounded border-gray-300 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                {{-- Buttons --}}
<div class="flex gap-3 pt-6 border-t border-gray-200 mt-6">
    <button type="submit" 
            style="background-color: #2563eb; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 500; border: none; cursor: pointer;">
        Save Brand
    </button>

    <a href="{{ route('brands.index') }}" 
       style="background-color: #e5e7eb; color: #1f2937; padding: 10px 24px; border-radius: 8px; font-weight: 500; text-decoration: none;">
        Cancel
    </a>
</div>
            </div>
        </form>
    </div>
</div>
@endsection