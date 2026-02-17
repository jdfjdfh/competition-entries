@extends('layouts.app')

@section('title', 'Вход в систему')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Вход в систему
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Или
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        зарегистрируйтесь
                    </a>
                </p>
            </div>
            <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" name="email" type="email" required
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                               placeholder="Email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Пароль</label>
                        <input id="password" name="password" type="password" required
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                               placeholder="Пароль">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Запомнить меня
                        </label>
                    </div>
                </div>

                @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Войти
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-100 text-gray-500">Тестовые данные</span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 text-xs text-gray-600">
                    <div class="text-center">
                        <p class="font-medium">Администратор</p>
                        <p>admin@contest.ru</p>
                        <p>password</p>
                    </div>
                    <div class="text-center">
                        <p class="font-medium">Жюри</p>
                        <p>maria.jury@contest.ru</p>
                        <p>password</p>
                    </div>
                    <div class="text-center">
                        <p class="font-medium">Участник 1</p>
                        <p>dmitry@example.com</p>
                        <p>password</p>
                    </div>
                    <div class="text-center">
                        <p class="font-medium">Участник 2</p>
                        <p>elena@example.com</p>
                        <p>password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
