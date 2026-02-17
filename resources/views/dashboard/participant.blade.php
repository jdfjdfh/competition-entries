@extends('layouts.app')

@section('title', 'Мой дашборд')

@section('content')
    <!-- Приветствие -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Здравствуйте, {{ Auth::user()->name }}!
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Добро пожаловать в ваш личный кабинет участника конкурсов
            </p>
        </div>
    </div>

    <!-- Кнопки быстрых действий -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('contests.index') }}"
           class="bg-white hover:bg-gray-50 border border-gray-200 rounded-lg p-6 text-center transition duration-150">
            <div class="text-indigo-600 text-3xl mb-2">📋</div>
            <h3 class="font-medium text-gray-900">Все конкурсы</h3>
            <p class="text-sm text-gray-500 mt-1">Просмотр доступных конкурсов</p>
        </a>

        @php
            $firstActiveContest = App\Models\Contest::where('is_active', true)->where('deadline_at', '>', now())->first();
        @endphp

        @if($firstActiveContest)
            <a href="{{ route('submissions.create', ['contest_id' => $firstActiveContest->id]) }}"
               class="bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg p-6 text-center transition duration-150">
                <div class="text-indigo-600 text-3xl mb-2">➕</div>
                <h3 class="font-medium text-indigo-900">Новая работа</h3>
                <p class="text-sm text-indigo-600 mt-1">Подать работу на конкурс</p>
            </a>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center opacity-50 cursor-not-allowed">
                <div class="text-gray-400 text-3xl mb-2">➕</div>
                <h3 class="font-medium text-gray-400">Новая работа</h3>
                <p class="text-sm text-gray-400 mt-1">Нет активных конкурсов</p>
            </div>
        @endif

        <a href="{{ route('submissions.index') }}"
           class="bg-white hover:bg-gray-50 border border-gray-200 rounded-lg p-6 text-center transition duration-150">
            <div class="text-indigo-600 text-3xl mb-2">📁</div>
            <h3 class="font-medium text-gray-900">Мои работы</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $my_submissions->count() }} работ подано</p>
        </a>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $my_submissions->count() }}</div>
            <div class="text-sm text-gray-600">Всего работ</div>
        </div>

        <div class="bg-yellow-50 rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $draft_count }}</div>
            <div class="text-sm text-yellow-700">Черновики</div>
        </div>

        <div class="bg-blue-50 rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-blue-600">{{ $submitted_count }}</div>
            <div class="text-sm text-blue-700">На проверке</div>
        </div>

        <div class="bg-green-50 rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-green-600">{{ $accepted_count }}</div>
            <div class="text-sm text-green-700">Принято</div>
        </div>

        <div class="bg-red-50 rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-red-600">{{ $needs_fix_count }}</div>
            <div class="text-sm text-red-700">Требуют доработки</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Мои последние работы -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Мои последние работы
                </h3>
                <a href="{{ route('submissions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    Все работы →
                </a>
            </div>

            <div class="border-t border-gray-200">
                @forelse($my_submissions as $submission)
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('submissions.show', $submission) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ $submission->title }}
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    Конкурс: {{ $submission->contest->title }} •
                                    {{ $submission->created_at->format('d.m.Y') }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($submission->status === 'accepted') bg-green-100 text-green-800
                            @elseif($submission->status === 'rejected') bg-red-100 text-red-800
                            @elseif($submission->status === 'submitted') bg-blue-100 text-blue-800
                            @elseif($submission->status === 'needs_fix') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $submission->status }}
                        </span>

                                @if($submission->isEditable())
                                    <a href="{{ route('submissions.edit', $submission) }}"
                                       class="text-indigo-600 hover:text-indigo-900 text-sm">
                                        Редактировать
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if($submission->attachments->count() > 0)
                            <div class="mt-2 flex items-center space-x-2 text-xs text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span>{{ $submission->attachments->count() }} файлов</span>
                                <span class="text-green-600">
                        ({{ $submission->attachments->where('status', 'scanned')->count() }} проверено)
                    </span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Нет работ</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Начните с подачи работы на конкурс
                        </p>
                        @if($firstActiveContest)
                            <div class="mt-4">
                                <a href="{{ route('submissions.create', ['contest_id' => $firstActiveContest->id]) }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Подать работу
                                </a>
                            </div>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Активные конкурсы -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium text-gray-900">
                    Активные конкурсы
                </h3>
            </div>

            <div class="border-t border-gray-200">
                @forelse($active_contests as $contest)
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('contests.show', $contest) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ $contest->title }}
                                </a>
                                <div class="mt-1 flex items-center space-x-2">
                        <span class="text-xs {{ $contest->deadline_color }}">
                            {{ $contest->deadline_icon }} {{ $contest->formatted_deadline }}
                        </span>
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs text-gray-500">
                            до {{ $contest->exact_deadline }}
                        </span>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                @php
                                    $diffInDays = now()->diffInDays($contest->deadline_at, false);
                                @endphp

                                @if($diffInDays <= 1)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Срочно!
                        </span>
                                @elseif($diffInDays <= 3)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            Скоро
                        </span>
                                @elseif($diffInDays <= 7)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $diffInDays }} дней
                        </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ floor($diffInDays / 7) }} {{ $contest->pluralForm(floor($diffInDays / 7), ['неделя', 'недели', 'недель']) }}
                        </span>
                                @endif

                                <a href="{{ route('submissions.create', ['contest_id' => $contest->id]) }}"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium py-1 px-3 rounded">
                                    Подать работу
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-500">Нет активных конкурсов</p>
                    </div>
                @endforelse

                @if($active_contests->count() > 0)
                    <div class="px-4 py-3 bg-gray-50 text-center">
                        <a href="{{ route('contests.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            Все конкурсы →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Последние уведомления -->
        <div class="lg:col-span-2 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Последние уведомления
                </h3>
                <a href="{{ route('notifications.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    Все уведомления →
                </a>
            </div>

            <div class="border-t border-gray-200">
                @forelse($recent_notifications as $notification)
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 last:border-0 {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3">
                                @if(is_null($notification->read_at))
                                    <span class="h-2 w-2 bg-blue-600 rounded-full mt-2"></span>
                                @endif
                                <div>
                                    <p class="text-sm text-gray-600">
                                        @if($notification->type === 'status_changed')
                                            @php $data = $notification->data; @endphp
                                            Статус работы
                                            <a href="{{ route('submissions.show', $data['submission_id'] ?? 0) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                                "{{ $data['submission_title'] ?? 'Без названия' }}"
                                            </a>
                                            изменен на
                                            <span class="font-medium
                                        @if(($data['new_status'] ?? '') === 'accepted') text-green-600
                                        @elseif(($data['new_status'] ?? '') === 'rejected') text-red-600
                                        @elseif(($data['new_status'] ?? '') === 'needs_fix') text-yellow-600
                                        @endif">
                                        {{ $data['new_status'] ?? 'неизвестно' }}
                                    </span>
                                        @else
                                            {{ $notification->type }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            @if(is_null($notification->read_at))
                                <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900">
                                        Отметить
                                    </button>
                                </form>
                            @endif
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
            </div>
        </div>
    </div>
@endsection
