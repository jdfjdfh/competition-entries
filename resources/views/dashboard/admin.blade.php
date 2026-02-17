@extends('layouts.app')

@section('title', 'Панель администратора')

@section('content')
    <!-- Приветствие -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Панель администратора
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Здравствуйте, {{ Auth::user()->name }}! Управление платформой.
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.clear-cache') }}"
                   onclick="event.preventDefault(); if(confirm('Очистить кэш?')) document.getElementById('clear-cache-form').submit();"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
                    🧹 Очистить кэш
                </a>
                <form id="clear-cache-form" action="{{ route('admin.clear-cache') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</div>
                    <div class="text-sm text-gray-600">Пользователей</div>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                <span class="text-green-600">{{ $stats['total_participants'] }}</span> участников •
                <span class="text-purple-600">{{ $stats['total_jury'] }}</span> жюри •
                <span class="text-red-600">{{ $stats['total_admins'] }}</span> админов
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_contests'] }}</div>
                    <div class="text-sm text-gray-600">Конкурсов</div>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                <span class="text-green-600">{{ $stats['active_contests'] }}</span> активных •
                <span class="text-gray-600">{{ $stats['total_contests'] - $stats['active_contests'] }}</span> завершено
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_submissions'] }}</div>
                    <div class="text-sm text-gray-600">Работ</div>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                <span class="text-yellow-600">{{ $stats['submissions_by_status']['submitted'] }}</span> на проверке •
                <span class="text-green-600">{{ $stats['submissions_by_status']['accepted'] }}</span> принято
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_attachments'] }}</div>
                    <div class="text-sm text-gray-600">Файлов</div>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                {{ round($stats['storage_used'] / 1024 / 1024, 2) }} MB использовано
            </div>
        </div>
    </div>

    <!-- Кнопки быстрых действий -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.users') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg p-4 text-center transition duration-150">
            <div class="text-2xl mb-1">👥</div>
            <h3 class="font-medium">Управление пользователями</h3>
        </a>

        <a href="{{ route('admin.contests') }}"
           class="bg-green-600 hover:bg-green-700 text-white rounded-lg p-4 text-center transition duration-150">
            <div class="text-2xl mb-1">🏆</div>
            <h3 class="font-medium">Управление конкурсами</h3>
        </a>

        <a href="{{ route('admin.submissions') }}"
           class="bg-purple-600 hover:bg-purple-700 text-white rounded-lg p-4 text-center transition duration-150">
            <div class="text-2xl mb-1">📋</div>
            <h3 class="font-medium">Все работы</h3>
        </a>

        <a href="{{ route('admin.statistics') }}"
           class="bg-orange-600 hover:bg-orange-700 text-white rounded-lg p-4 text-center transition duration-150">
            <div class="text-2xl mb-1">📊</div>
            <h3 class="font-medium">Статистика</h3>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Последние пользователи -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Последние регистрации
                </h3>
                <a href="{{ route('admin.users') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    Все пользователи →
                </a>
            </div>

            <div class="border-t border-gray-200">
                @forelse($stats['recent_users'] as $user)
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($user->role === 'admin') bg-red-100 text-red-800
                            @elseif($user->role === 'jury') bg-purple-100 text-purple-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ $user->role }}
                        </span>
                                <span class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-500">Нет пользователей</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Последние работы -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Последние работы
                </h3>
                <a href="{{ route('admin.submissions') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    Все работы →
                </a>
            </div>

            <div class="border-t border-gray-200">
                @forelse($stats['recent_submissions'] as $submission)
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <a href="{{ route('submissions.show', $submission) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ $submission->title }}
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $submission->user->name }} • {{ $submission->contest->title }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($submission->status === 'accepted') bg-green-100 text-green-800
                            @elseif($submission->status === 'rejected') bg-red-100 text-red-800
                            @elseif($submission->status === 'submitted') bg-blue-100 text-blue-800
                            @elseif($submission->status === 'needs_fix') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $submission->status }}
                        </span>
                                <span class="text-xs text-gray-500">{{ $submission->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-500">Нет работ</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Статусы работ -->
        <div class="lg:col-span-2 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium text-gray-900">
                    Распределение работ по статусам
                </h3>
            </div>

            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="grid grid-cols-5 gap-2 text-center mb-4">
                    <div class="bg-gray-100 p-3 rounded">
                        <div class="text-xl font-bold text-gray-700">{{ $stats['submissions_by_status']['draft'] }}</div>
                        <div class="text-xs text-gray-600">Черновики</div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded">
                        <div class="text-xl font-bold text-blue-700">{{ $stats['submissions_by_status']['submitted'] }}</div>
                        <div class="text-xs text-blue-600">На проверке</div>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded">
                        <div class="text-xl font-bold text-yellow-700">{{ $stats['submissions_by_status']['needs_fix'] }}</div>
                        <div class="text-xs text-yellow-600">Доработка</div>
                    </div>
                    <div class="bg-green-100 p-3 rounded">
                        <div class="text-xl font-bold text-green-700">{{ $stats['submissions_by_status']['accepted'] }}</div>
                        <div class="text-xs text-green-600">Принято</div>
                    </div>
                    <div class="bg-red-100 p-3 rounded">
                        <div class="text-xl font-bold text-red-700">{{ $stats['submissions_by_status']['rejected'] }}</div>
                        <div class="text-xs text-red-600">Отклонено</div>
                    </div>
                </div>

                <!-- Прогресс-бар всех работ -->
                @php
                    $total = array_sum($stats['submissions_by_status']);
                    $accepted_percent = $total > 0 ? round(($stats['submissions_by_status']['accepted'] / $total) * 100) : 0;
                    $rejected_percent = $total > 0 ? round(($stats['submissions_by_status']['rejected'] / $total) * 100) : 0;
                    $pending_percent = $total > 0 ? round(($stats['submissions_by_status']['submitted'] / $total) * 100) : 0;
                @endphp

                @if($total > 0)
                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>Принято: {{ $accepted_percent }}%</span>
                            <span>На проверке: {{ $pending_percent }}%</span>
                            <span>Отклонено: {{ $rejected_percent }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $accepted_percent }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
