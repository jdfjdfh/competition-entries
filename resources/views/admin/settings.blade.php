@extends('layouts.app')

@section('title', 'Настройки')

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Настройки системы
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Управление параметрами платформы
            </p>
        </div>

        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-4">Основные настройки</h4>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="site_name" class="block text-sm font-medium text-gray-700">Название сайта</label>
                                <input type="text" name="site_name" id="site_name" value="Конкурс работ"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="site_email" class="block text-sm font-medium text-gray-700">Email для уведомлений</label>
                                <input type="email" name="site_email" id="site_email" value="noreply@contest.ru"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-4">Настройки файлов</h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="max_files" class="block text-sm font-medium text-gray-700">Максимум файлов на работу</label>
                                <input type="number" name="max_files" id="max_files" value="3" min="1" max="10"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="max_file_size" class="block text-sm font-medium text-gray-700">Макс. размер файла (MB)</label>
                                <input type="number" name="max_file_size" id="max_file_size" value="10" min="1" max="100"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Разрешенные типы файлов</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="allowed_types[]" value="pdf" checked
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-gray-900">PDF</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="allowed_types[]" value="zip" checked
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-gray-900">ZIP</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="allowed_types[]" value="png" checked
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-gray-900">PNG</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="allowed_types[]" value="jpg" checked
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-gray-900">JPG</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end">
                    <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700">
                        Сохранить настройки
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
