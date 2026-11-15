@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Categories</h2>
        <p class="text-sm text-slate-500 mt-1">Manage product categories</p>
    </div>
    <a href="{{ route('categories.create') }}" class="btn-primary-soft">+ Add Category</a>
</div>

<div class="panel overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Slug</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($categories as $category)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $category->name }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $category->slug }}</td>
                    <td class="px-4 py-3">
                        @if($category->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:text-blue-800 font-medium mr-3">Edit</a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                        No categories yet. <a href="{{ route('categories.create') }}" class="text-blue-600 hover:underline">Create one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($categories, 'links'))
    <div class="px-4 py-3 border-t border-slate-100">{{ $categories->links() }}</div>
    @endif
</div>
@endsection