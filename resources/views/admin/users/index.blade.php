@extends('layouts.admin')

@section('title', 'Users Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Users Management</h1>
    <a href="{{ route('admin.users.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
        Create New User
    </a>
</div>

<!-- Search & Filter -->
<div class="mb-6 bg-white rounded-lg shadow p-4">
    <form action="" method="GET" class="flex gap-4">
        <div class="flex-1">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search users..." 
                   class="w-full border-gray-300 rounded-lg shadow-sm">
        </div>
        <div class="w-48">
            <select name="role" 
                    class="w-full border-gray-300 rounded-lg shadow-sm">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->slug }}" 
                            {{ request('role') === $role->slug ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" 
                class="bg-gray-500 text-white px-4 py-2 rounded-lg">
            Filter
        </button>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    User
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Roles
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Last Login
                </th>
                <th class="px-6 py-3 bg-gray-50"></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($users as $user)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full" 
                                 src="{{ $user->avatar_url }}" 
                                 alt="">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $user->name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $user->email }}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1">
                        @foreach($user->roles as $role)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $user->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="text-indigo-600 hover:text-indigo-900">
                        Edit
                    </a>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}" 
                          method="POST" 
                          class="inline ml-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 hover:text-red-900"
                                onclick="return confirm('Are you sure you want to delete this user?')">
                            Delete
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection 