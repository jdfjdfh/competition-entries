@extends('layouts.app')

@section('title', 'Главная')

@section('content')
    <div class="bg-white overflow-hidden">
        <!-- Герой-секция -->
        <div class="relative bg-indigo-800">
            <div class="absolute inset-0">
                <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Конкурс">
                <div class="absolute inset-0 bg-indigo-800 mix-blend-multiply"></div>
            </div>

            <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Платформа «Сбор работ на конкурс»
                </h1>
                <p class="mt-6 text-xl text-indigo-100 max-w-3xl">
                    Удобный инструмент для организации и проведения конкурсов различных направлений. Подавайте работы, отслеживайте статусы, получайте обратную связь.
                </p>

                @guest
                    <div class="mt-10 flex space-x-4">
                        <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50">
                            Начать участие
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 bg-opacity-60 hover:bg-opacity-70">
                            Войти
                        </a>
                    </div>
                @endguest
            </div>
        </div>

        <!-- Как это работает -->
        <div class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-base font-semibold text-indigo-600 tracking-wide uppercase">Как это работает</h2>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900 sm:text-4xl">
                        Простой процесс участия
                    </p>
                </div>

                <div class="mt-12">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div class="text-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600 mx-auto">
                                <span class="text-2xl font-bold">1</span>
                            </div>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">Выберите конкурс</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Просмотрите активные конкурсы и выберите подходящий для участия
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600 mx-auto">
                                <span class="text-2xl font-bold">2</span>
                            </div>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">Загрузите работу</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Создайте заявку и загрузите до 3 файлов (PDF, ZIP, PNG, JPG до 10MB)
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600 mx-auto">
                                <span class="text-2xl font-bold">3</span>
                            </div>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">Получите результат</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Отслеживайте статус работы и получайте обратную связь от жюри
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Активные конкурсы -->
        @php
            $activeContests = App\Models\Contest::where('is_active', true)
                                ->where('deadline_at', '>', now())
                                ->latest()
                                ->take(3)
                                ->get();
        @endphp

        @if($activeContests->count() > 0)
            <div class="py-16 bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h2 class="text-base font-semibold text-indigo-600 tracking-wide uppercase">Актуально</h2>
                        <p class="mt-2 text-3xl font-extrabold text-gray-900 sm:text-4xl">
                            Активные конкурсы
                        </p>
                        <p class="mt-4 text-lg text-gray-500">
                            Примите участие в текущих конкурсах
                        </p>
                    </div>

                    <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($activeContests as $contest)
                            <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300">
                                <div class="px-6 py-8">
                                    <h3 class="text-xl font-bold text-gray-900">{{ $contest->title }}</h3>
                                    <p class="mt-3 text-gray-600">{{ Str::limit($contest->description, 100) }}</p>

                                    <div class="mt-6 flex items-center text-sm text-gray-500">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Дедлайн: {{ $contest->deadline_at->format('d.m.Y H:i') }}
                                    </div>

                                    <div class="mt-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ $contest->deadline_at->diffInDays(now()) }} дней осталось
                            </span>
                                    </div>
                                </div>

                                <div class="px-6 py-4 bg-gray-50 border-t">
                                    <a href="{{ route('contests.show', $contest) }}" class="text-indigo-600 hover:text-indigo-900 font-medium flex items-center justify-between">
                                        <span>Подробнее о конкурсе</span>
                                        <span>→</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-12 text-center">
                        <a href="{{ route('contests.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                            Все конкурсы
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Преимущества -->
        <div class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-indigo-600 font-semibold tracking-wide uppercase">Преимущества</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Все необходимое для конкурса
                    </p>
                </div>

                <div class="mt-12">
                    <div class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                        <div class="relative">
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-16">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Удобная подача работ</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Простой и понятный интерфейс для создания и редактирования заявок
                                </p>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-16">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Поддержка файлов</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Загружайте до 3 файлов форматов PDF, ZIP, PNG, JPG до 10MB каждый
                                </p>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-16">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Отслеживание статуса</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Всегда знайте, на каком этапе рассмотрения ваша работа
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Призыв к действию -->
        @guest
            <div class="bg-indigo-50">
                <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        <span class="block">Готовы принять участие?</span>
                        <span class="block text-indigo-600">Присоединяйтесь к конкурсам прямо сейчас.</span>
                    </h2>
                    <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                        <div class="inline-flex rounded-md shadow">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Зарегистрироваться
                            </a>
                        </div>
                        <div class="ml-3 inline-flex rounded-md shadow">
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50">
                                Войти
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endguest
    </div>
@endsection
