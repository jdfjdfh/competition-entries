@extends('layouts.app')

@section('title', 'Панель жюри')

@section('content')
    <!-- Приветствие -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Панель жюри
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Здравствуйте, {{ Auth::user()->name }}! Здесь вы можете просматривать и оценивать работы участников.
            </p>
        </div>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $total_submissions }}</div>
            <div class="text-sm text-gray-600">Всего работ</div>
        </div>

        <div class="bg-blue-50 rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-blue-600">{{ $pending_review }}</div>
            <div class="text-sm text-blue-700">Ожидают проверки</div>
            @if($pending_review > 0)
                <a href="{{ route('submissions.index') }}?status=submitted" class="text-xs text-blue-600 hover:text-blue-800 mt-1 inline-block">
                    Проверить →
                </a>
            @endif
        </div>

        <div class="bg-yellow-50 rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-yellow-600">{{ $needs_fix }}</div>
            <div class="text-sm text-yellow-700">Требуют доработки</div>
            @if($needs_fix > 0)
                <a href="{{ route('submissions.index') }}?status=needs_fix" class="text-xs text-yellow-600 hover:text-yellow-800 mt-1 inline-block">
                    Просмотреть →
                </a>
            @endif
        </div>

        <div class="bg-green-50 rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-green-600">{{ $accepted }}</div>
            <div class="text-sm text-green-700">Принято</div>
        </div>

        <div class="bg-red-50 rounded-lg shadow p-4">
            <div class="text-2xl font-bold text-red-600">{{ $rejected }}</div>
            <div class="text-sm text-red-700">Отклонено</div>
        </div>
    </div>

    <!-- Кнопки быстрых действий -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('submissions.index') }}?status=submitted"
           class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg p-6 text-center transition duration-150">
            <div class="text-3xl mb-2">📋</div>
            <h3 class="font-medium text-lg">Работы на проверку</h3>
            <p class="text-sm text-blue-100 mt-1">{{ $pending_review }} ожидают решения</p>
        </a>

        <a href="{{ route('submissions.index') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg p-6 text-center transition duration-150">
            <div class="text-3xl mb-2">📁</div>
            <h3 class="font-medium text-lg">Все работы</h3>
            <p class="text-sm text-indigo-100 mt-1">Просмотр всех поданных работ</p>
        </a>

        <a href="{{ route('contests.index') }}"
           class="bg-purple-600 hover:bg-purple-700 text-white rounded-lg p-6 text-center transition duration-150">
            <div class="text-3xl mb-2">🏆</div>
            <h3 class="font-medium text-lg">Конкурсы</h3>
            <p class="text-sm text-purple-100 mt-1">Список всех конкурсов</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Работы, ожидающие проверки -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Ожидают проверки
                </h3>
                <a href="{{ route('submissions.index') }}?status=submitted" class="text-sm text-indigo-600 hover:text-indigo-900">
                    Все на проверке →
                </a>
            </div>

            <div class="border-t border-gray-200">
                @forelse($submissions_for_review as $submission)
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('submissions.show', $submission) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ $submission->title }}
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    Конкурс: {{ $submission->contest->title }} •
                                    Автор: {{ $submission->user->name }} •
                                    {{ $submission->created_at->format('d.m.Y') }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $submission->attachments->count() }} файлов
                        </span>

                                <a href="{{ route('submissions.show', $submission) }}"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium py-1 px-3 rounded">
                                    Оценить
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Нет работ на проверке</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Все работы проверены
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Последняя активность -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium text-gray-900">
                    Последняя активность
                </h3>
            </div>

            <div class="border-t border-gray-200">
                @forelse($recent_activity as $submission)
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <a href="{{ route('submissions.show', $submission) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ $submission->title }}
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $submission->user->name }} • {{ $submission->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        @if($submission->status === 'accepted') bg-green-100 text-green-800
                        @elseif($submission->status === 'rejected') bg-red-100 text-red-800
                        @elseif($submission->status === 'submitted') bg-blue-100 text-blue-800
                        @elseif($submission->status === 'needs_fix') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $submission->status }}
                    </span>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-500">Нет активности</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Статистика по конкурсам -->
        <div class="lg:col-span-2 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium text-gray-900">
                    Статистика по конкурсам
                </h3>
            </div>

            <div class="border-t border-gray-200">
                @php
                    $contests = App\Models\Contest::withCount('submissions')->latest()->take(5)->get();
                @endphp

                @foreach($contests as $contest)
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-gray-900">{{ $contest->title }}</h4>
                            <span class="text-xs text-gray-500">Дедлайн: {{ $contest->deadline_at->format('d.m.Y') }}</span>
                        </div>

                        @php
                            $total = $contest->submissions_count;
                            $accepted = $contest->submissions()->where('status', 'accepted')->count();
                            $rejected = $contest->submissions()->where('status', 'rejected')->count();
                            $pending = $contest->submissions()->where('status', 'submitted')->count();
                            $needs_fix = $contest->submissions()->where('status', 'needs_fix')->count();
                        @endphp

                        <div class="grid grid-cols-4 gap-2 text-center text-xs">
                            <div>
                                <div class="font-medium text-gray-900">{{ $total }}</div>
                                <div class="text-gray-500">всего</div>
                            </div>
                            <div>
                                <div class="font-medium text-green-600">{{ $accepted }}</div>
                                <div class="text-gray-500">принято</div>
                            </div>
                            <div>
                                <div class="font-medium text-blue-600">{{ $pending }}</div>
                                <div class="text-gray-500">в работе</div>
                            </div>
                            <div>
                                <div class="font-medium text-yellow-600">{{ $needs_fix }}</div>
                                <div class="text-gray-500">доработка</div>
                            </div>
                        </div>

                        <!-- Прогресс-бар -->
                        @if($total > 0)
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ ($accepted / $total) * 100 }}%"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
