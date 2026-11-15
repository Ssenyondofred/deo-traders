@extends('layouts.admin')

@section('title', 'Add Supplier')

@section('content')
<div style="max-width:640px;background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
    <h1 style="font-size:22px;font-weight:700;margin-bottom:16px;">Add Supplier</h1>

    @if ($errors->any())
        <div style="margin-bottom:16px;padding:12px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf

        <label style="display:block;margin-bottom:6px;font-weight:600;">Supplier Name *</label>
        <input type="text" name="name" value="{{ old('name') }}" required
               style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px;margin-bottom:12px;">

        <label style="display:block;margin-bottom:6px;font-weight:600;">Contact Person</label>
        <input type="text" name="contact_person" value="{{ old('contact_person') }}"
               style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px;margin-bottom:12px;">

        <label style="display:block;margin-bottom:6px;font-weight:600;">Phone</label>
        <input type="text" name="phone" value="{{ old('phone') }}"
               style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px;margin-bottom:12px;">

        <label style="display:block;margin-bottom:6px;font-weight:600;">Email</label>
        <input type="email" name="email" value="{{ old('email') }}"
               style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px;margin-bottom:12px;">

        <label style="display:block;margin-bottom:6px;font-weight:600;">Tax Number</label>
        <input type="text" name="tax_number" value="{{ old('tax_number') }}"
               style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px;margin-bottom:12px;">

        <label style="display:block;margin-bottom:6px;font-weight:600;">Address</label>
        <textarea name="address" rows="3"
                  style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px;margin-bottom:12px;">{{ old('address') }}</textarea>

        <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            Active
        </label>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                    style="background:#16a34a;color:white;padding:10px 18px;border:none;border-radius:8px;font-weight:700;">
                Save Supplier
            </button>
            <a href="{{ route('suppliers.index') }}"
               style="background:#f3f4f6;color:#374151;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:600;">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection