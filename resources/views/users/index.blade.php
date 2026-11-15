@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Users</h2>
        <p class="text-sm text-slate-500 mt-1">System users</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn-primary-soft">+ Add User</a>
</div>

<div class="panel p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Name</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Email</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Created</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b border-slate-100">
                        <td class="px-3 py-2">{{ $user->name }}</td>
                        <td class="px-3 py-2">{{ $user->email }}</td>
                        <td class="px-3 py-2">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('users.edit', $user) }}" class="text-slate-600 text-xs">Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 text-xs ml-2" onclick="return confirm('Delete user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-slate-500">No users yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection