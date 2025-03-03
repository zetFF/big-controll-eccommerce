@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Notifications</h2>
                    @if($notifications->where('read_at', null)->count() > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-900">
                                Mark all as read
                            </button>
                        </form>
                    @endif
                </div>

                @if($notifications->isEmpty())
                    <p class="text-gray-500 text-center py-4">No notifications found.</p>
                @else
                    <div class="space-y-4">
                        @foreach($notifications as $notification)
                            <div class="flex items-start p-4 {{ $notification->isUnread() ? 'bg-blue-50' : 'bg-white' }} rounded-lg">
                                <div class="flex-shrink-0">
                                    @if($notification->type === 'success')
                                        <span class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    @elseif($notification->type === 'warning')
                                        <span class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $notification->title }}
                                        </p>
                                        <div class="ml-2 flex-shrink-0 flex">
                                            @if($notification->isUnread())
                                                <form action="{{ route('notifications.mark-as-read', $notification) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 text-sm">
                                                        Mark as read
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline ml-3">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $notification->message }}
                                    </p>
                                    <p class="mt-2 text-xs text-gray-400">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 