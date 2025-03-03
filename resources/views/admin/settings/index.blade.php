@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">System Settings</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.settings.export') }}" 
           class="bg-green-500 text-white px-4 py-2 rounded-lg">
            Export Settings
        </a>
        <button type="button"
                onclick="document.getElementById('import-form').click()"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg">
            Import Settings
        </button>
        <form action="{{ route('admin.settings.import') }}"
              method="POST"
              enctype="multipart/form-data"
              class="hidden">
            @csrf
            <input type="file"
                   id="import-form"
                   name="file"
                   accept=".json"
                   onchange="this.form.submit()">
        </form>
    </div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        @foreach($settings as $group => $groupSettings)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-lg font-medium">{{ ucfirst($group) }} Settings</h2>
            </div>
            <div class="p-6 space-y-4">
                @foreach($groupSettings as $setting)
                <div class="grid grid-cols-3 gap-4 items-start">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $setting->description ?? ucwords(str_replace('_', ' ', $setting->key)) }}
                        </label>
                        @if($setting->description)
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $setting->description }}
                        </p>
                        @endif
                    </div>
                    <div class="col-span-2">
                        @switch($setting->type)
                            @case('boolean')
                                <label class="inline-flex items-center">
                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                    <input type="checkbox"
                                           name="settings[{{ $setting->key }}]"
                                           value="1"
                                           {{ $setting->value ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2">Enabled</span>
                                </label>
                                @break

                            @case('select')
                                <select name="settings[{{ $setting->key }}]"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    @foreach($setting->options as $value => $label)
                                        <option value="{{ $value }}"
                                                {{ $setting->value == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @break

                            @case('textarea')
                                <textarea name="settings[{{ $setting->key }}]"
                                          rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">{{ $setting->value }}</textarea>
                                @break

                            @default
                                <input type="{{ $setting->type }}"
                                       name="settings[{{ $setting->key }}]"
                                       value="{{ $setting->value }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        @endswitch
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg">
            Save Settings
        </button>
    </div>
</form>
@endsection 