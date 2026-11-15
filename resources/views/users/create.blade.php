@extends('layouts.admin')

@section('title', 'Add User')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Add User</h2>
</div>

<div class="panel p-5 max-w-md">
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-primary-soft">Save User</button>
    </form>
</div>
@endsection