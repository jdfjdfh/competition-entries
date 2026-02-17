@extends('layouts.app')

@section('title', 'Уведомления')

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Уведомления
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Все ваши уведомления
                </p>
            </div>
            @if($notifications->count() > 0)
                <div class="flex space-x-2">
                    <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-4 rounded">
                            Отметить все как прочитанные
                        </button>
                    </form>
                    <form action="{{ route('notifications.clear-all') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded" onclick="return confirm('Удалить все уведомления?')">
                            Очистить все
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="border-t border-gray-200">
            @forelse($notifications as $notification)
                <div class="px-4 py-4 sm:px-6 {{ is_null($notification->read_at) ? 'bg-blue-50' : 'bg-white' }} border-b border-gray-200 last:border-0">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                @if(is_null($notification->read_at))
                                    <span class="h-2 w-2 bg-blue-600 rounded-full mr-2"></span>
                                @endif
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $notification->type === 'status_changed' ? 'Изменение статуса работы' : 'Уведомление' }}
                                </p>
                            </div>
                            <div class="mt-2">
                                @if($notification->type === 'status_changed')
                                    @php
                                        $data = $notification->data;
                                    @endphp
                                    <p class="text-sm text-gray-600">
                                        Статус работы "{{ $data['submission_title'] ?? 'Без названия' }}"
                                        изменен с "{{ $data['old_status'] ?? 'неизвестно' }}" на
                                        <span class="font-medium
                                    @if(($data['new_status'] ?? '') === 'accepted') text-green-600
                                    @elseif(($data['new_status'] ?? '') === 'rejected') text-red-600
                                    @elseif(($data['new_status'] ?? '') === 'needs_fix') text-yellow-600
                                    @else text-blue-600 @endif">
                                    {{ $data['new_status'] ?? 'неизвестно' }}
                                </span>
                                    </p>
                                @else
                                    <p class="text-sm text-gray-600">{{ json_encode($notification->data) }}</p>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="ml-4 flex items-center space-x-2">
                            @if(is_null($notification->read_at))
                                <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-900">
                                        Отметить
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-900">
                                    Удалить
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет уведомлений</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        У вас пока нет уведомлений
                    </p>
                </div>
            @endforelse

            @if($notifications->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
