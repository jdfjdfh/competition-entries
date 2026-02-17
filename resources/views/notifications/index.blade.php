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
                <div class="px-4 py-4 sm:px-6 border-b border-gray-200 hover:bg-gray-50 transition-colors {{ is_null($notification->read_at) ? $notification->bg_color : '' }}">
                    <div class="flex items-start space-x-3">
                        <!-- Иконка -->
                        <div class="flex-shrink-0 text-2xl">
                            {{ $notification->icon }}
                        </div>

                        <!-- Контент -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">
                                    @switch($notification->type)
                                        @case('status_changed')
                                            Изменение статуса
                                            @break
                                        @case('new_comment')
                                            Новый комментарий
                                            @break
                                        @case('new_submission')
                                            Новая работа
                                            @break
                                        @case('deadline_reminder')
                                            Напоминание
                                            @break
                                        @default
                                            Уведомление
                                    @endswitch
                                </p>

                                <div class="flex items-center space-x-2">
                                    @if(is_null($notification->read_at))
                                        <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900">
                                                Отметить
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-900">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Текст уведомления со ссылкой -->
                            <a href="{{ $notification->link }}" class="block mt-1 group">
                                <p class="text-sm text-gray-600 group-hover:text-indigo-600 transition-colors">
                                    {{ $notification->message }}
                                </p>

                                <!-- Для уведомлений об изменении статуса показываем старый и новый статус -->
                                @if($notification->type === 'status_changed' && isset($notification->data['old_status']))
                                    <div class="mt-2 flex items-center space-x-2">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $notification->old_status_color }}">
                                    {{ $notification->old_status_name }}
                                </span>
                                        <span class="text-gray-400">→</span>
                                        <span class="px-2 py-0.5 text-xs rounded-full {{ $notification->new_status_color }}">
                                    {{ $notification->new_status_name }}
                                </span>
                                    </div>
                                @endif

                                <!-- Для новых комментариев показываем превью -->
                                @if($notification->type === 'new_comment' && isset($notification->data['comment_preview']))
                                    <p class="mt-2 text-xs text-gray-500 italic">
                                        "{{ Str::limit($notification->data['comment_preview'], 50) }}"
                                    </p>
                                @endif

                                <!-- Для напоминаний о дедлайне показываем дату -->
                                @if($notification->type === 'deadline_reminder' && isset($notification->data['deadline']))
                                    <p class="mt-2 text-xs text-gray-500">
                                        Дедлайн: {{ $notification->data['deadline'] }}
                                    </p>
                                @endif
                            </a>

                            <!-- Дата -->
                            <p class="mt-2 text-xs text-gray-400">
                                {{ $notification->created_at->format('d.m.Y H:i') }}
                                <span class="ml-1">({{ $notification->created_at->diffForHumans() }})</span>
                            </p>
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
