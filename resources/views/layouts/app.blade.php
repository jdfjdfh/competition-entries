<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Конкурс работ')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <!-- Левая часть навигации -->
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800">
                        🏆 Конкурс работ
                    </a>
                </div>

                @auth
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 {{ request()->routeIs('dashboard') ? 'border-b-2 border-indigo-500' : '' }}">
                            📊 Дашборд
                        </a>

                        <a href="{{ route('contests.index') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 {{ request()->routeIs('contests.*') ? 'border-b-2 border-indigo-500' : '' }}">
                            📋 Конкурсы
                        </a>

                        @if(Auth::user()->isParticipant() || Auth::user()->isAdmin())
                            <a href="{{ route('submissions.index') }}"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 {{ request()->routeIs('submissions.index') ? 'border-b-2 border-indigo-500' : '' }}">
                                📁 Мои работы
                            </a>
                        @endif

                        @if(Auth::user()->isJury() || Auth::user()->isAdmin())
                            <a href="{{ route('submissions.index') }}?status=submitted"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                ⏳ На проверке
                            </a>
                        @endif

                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.users') }}"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 {{ request()->routeIs('admin.users*') ? 'border-b-2 border-indigo-500' : '' }}">
                                👥 Пользователи
                            </a>

                            <a href="{{ route('admin.contests') }}"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 {{ request()->routeIs('admin.contests*') ? 'border-b-2 border-indigo-500' : '' }}">
                                🏆 Управление конкурсами
                            </a>

                            <a href="{{ route('admin.statistics') }}"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 {{ request()->routeIs('admin.statistics') ? 'border-b-2 border-indigo-500' : '' }}">
                                📊 Статистика
                            </a>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Правая часть навигации (профиль и уведомления) -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- Уведомления -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            @php
                                $unreadCount = App\Models\Notification::where('user_id', Auth::id())->whereNull('read_at')->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                {{ $unreadCount }}
                            </span>
                            @endif
                        </button>

                        <!-- Выпадающее меню уведомлений -->
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-20" style="display: none;">
                            <div class="py-2">
                                <div class="px-4 py-2 bg-gray-50 border-b">
                                    <h3 class="text-sm font-medium text-gray-700">Уведомления</h3>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    @php
                                        $latestNotifications = App\Models\Notification::where('user_id', Auth::id())->latest()->limit(5)->get();
                                    @endphp
                                    @forelse($latestNotifications as $notification)
                                        <div class="px-4 py-3 hover:bg-gray-50 {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }}">
                                            <p class="text-sm text-gray-600">
                                                @if($notification->type === 'status_changed')
                                                    @php $data = $notification->data; @endphp
                                                    Статус работы "{{ Str::limit($data['submission_title'] ?? 'Без названия', 30) }}" изменен
                                                @else
                                                    {{ $notification->type }}
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    @empty
                                        <div class="px-4 py-8 text-center text-sm text-gray-500">
                                            Нет уведомлений
                                        </div>
                                    @endforelse
                                </div>
                                <div class="px-4 py-2 bg-gray-50 border-t text-center">
                                    <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-900">
                                        Все уведомления
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Профиль -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                            <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Выпадающее меню профиля -->
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20" style="display: none;">
                            <div class="px-4 py-2 border-b">
                                <p class="text-xs text-gray-500">Роль: {{ Auth::user()->role_name }}</p>
                            </div>

                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                📊 Дашборд
                            </a>

                            <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                🔔 Уведомления
                            </a>

                            @if(Auth::user()->isParticipant())
                                @php
                                    $firstActiveContest = App\Models\Contest::where('is_active', true)->where('deadline_at', '>', now())->first();
                                @endphp
                                @if($firstActiveContest)
                                    <a href="{{ route('submissions.create', ['contest_id' => $firstActiveContest->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        ➕ Новая работа
                                    </a>
                                @endif
                            @endif

                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.contests.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    ➕ Новый конкурс
                                </a>
                                <a href="{{ route('admin.users.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    ➕ Новый пользователь
                                </a>
                            @endif

                            @if(Auth::user()->isJury())
                                <a href="{{ route('submissions.index') }}?status=submitted" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    ⏳ Проверить работы
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="border-t">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    🚪 Выйти
                                </button>
                            </form>
                        </div>
                    </div>

                @else
                    <!-- Гость -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">Вход</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Регистрация
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- Добавляем Alpine.js для интерактивности -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<main class="py-6">
    <div class="max-w-7xl mx-auto px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</main>



</body>
</html>
