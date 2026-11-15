@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Edit User</h2>
</div>

<div class="panel p-5 max-w-md">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input type="text" name="name" value="{{ $user->name }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ $user->email }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">New Password (leave blank to keep)</label>
            <input type="password" name="password" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-primary-soft">Update User</button>
    </form>
</div>
@endsection