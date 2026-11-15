@extends('layouts.admin')

@section('title', 'Units')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Units</h2>
        <p class="text-sm text-slate-500 mt-1">Manage measurement units</p>
    </div>
    <a href="{{ route('units.create') }}" class="btn-primary-soft">+ Add Unit</a>
</div>

<div class="panel overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Short name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($units as $unit)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $unit->name }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $unit->short_name }}</td>
                    <td class="px-4 py-3">
                        @if($unit->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('units.edit', $unit) }}" class="text-blue-600 hover:text-blue-800 font-medium mr-3">Edit</a>
                        <form action="{{ route('units.destroy', $unit) }}" method="POST" class="inline" onsubmit="return confirm('Delete this unit?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                        No units yet. <a href="{{ route('units.create') }}" class="text-blue-600 hover:underline">Create one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($units, 'links'))
    <div class="px-4 py-3 border-t border-slate-100">{{ $units->links() }}</div>
    @endif
</div>
@endsection