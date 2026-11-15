@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Customers</h1>
    <a href="{{ route('customers.create') }}" 
       style="background-color: #2563eb; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px;">
        + Add Customer
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit Limit</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $customer->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $customer->email ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($customer->credit_limit, 2) }}</td>
                        <td class="px-4 py-3 text-sm {{ $customer->current_balance > 0 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                            {{ number_format($customer->current_balance, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            @if($customer->is_active)
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium">
                            <a href="{{ route('customers.edit', $customer) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this customer?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                            No customers found. 
                            <a href="{{ route('customers.create') }}" class="text-blue-600 hover:underline">Create one</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($customers->hasPages())
    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@endif
@endsection