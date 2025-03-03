@extends('layouts.admin')

@section('title', 'Backup Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Backup Management</h1>
    <a href="{{ route('admin.backups.create') }}" 
       class="bg-blue-500 text-white px-4 py-2 rounded-lg">
        Create New Backup
    </a>
</div>

<div class="bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Size
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Created
                </th>
                <th class="px-6 py-3 bg-gray-50"></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($backups as $backup)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        {{ $backup->name }}
                    </div>
                    <div class="text-sm text-gray-500">
                        Created by {{ $backup->creator->name }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $backup->type === 'database' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($backup->type) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $backup->size_for_humans }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        @if($backup->status === 'completed') bg-green-100 text-green-800
                        @elseif($backup->status === 'failed') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ ucfirst($backup->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $backup->created_at->diffForHumans() }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.backups.download', $backup) }}" 
                       class="text-blue-600 hover:text-blue-900">Download</a>
                    
                    @if($backup->status === 'completed')
                    <form action="{{ route('admin.backups.restore', $backup) }}" 
                          method="POST" 
                          class="inline ml-3"
                          onsubmit="return confirm('Are you sure you want to restore this backup? This will overwrite current data.')">
                        @csrf
                        <button type="submit" 
                                class="text-green-600 hover:text-green-900">
                            Restore
                        </button>
                    </form>
                    @endif

                    <form action="{{ route('admin.backups.destroy', $backup) }}" 
                          method="POST" 
                          class="inline ml-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-red-600 hover:text-red-900"
                                onclick="return confirm('Are you sure you want to delete this backup?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $backups->links() }}
</div>
@endsection 