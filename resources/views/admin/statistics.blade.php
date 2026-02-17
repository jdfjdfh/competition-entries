@extends('layouts.app')

@section('title', 'Статистика')

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Статистика платформы
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Детальная статистика по всем разделам
            </p>
        </div>
    </div>

    <!-- Пользователи -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900">
                👥 Пользователи
            </h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['users']['total'] }}</div>
                    <div class="text-sm text-gray-600">Всего</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['users']['by_role']['participant'] }}</div>
                    <div class="text-sm text-green-700">Участники</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ $stats['users']['by_role']['jury'] }}</div>
                    <div class="text-sm text-purple-700">Жюри</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['users']['by_role']['admin'] }}</div>
                    <div class="text-sm text-red-700">Админы</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['users']['new_this_month'] }}</div>
                    <div class="text-sm text-blue-700">За месяц</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Конкурсы -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900">
                🏆 Конкурсы
            </h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['contests']['total'] }}</div>
                    <div class="text-sm text-gray-600">Всего</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['contests']['active'] }}</div>
                    <div class="text-sm text-green-700">Активных</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['contests']['expired'] }}</div>
                    <div class="text-sm text-red-700">Завершено</div>
                </div>
            </div>

            <h4 class="text-sm font-medium text-gray-700 mb-2">Конкурсы по месяцам</h4>
            <div class="grid grid-cols-12 gap-1">
                @for($i = 1; $i <= 12; $i++)
                    <div class="text-center">
                        <div class="text-xs text-gray-500">{{ date('M', mktime(0, 0, 0, $i, 1)) }}</div>
                        <div class="mt-1 text-sm font-medium">{{ $stats['contests']['by_month'][$i] ?? 0 }}</div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Работы -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900">
                📋 Работы
            </h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['submissions']['total'] }}</div>
                    <div class="text-sm text-gray-600">Всего</div>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <div class="text-xl font-bold text-gray-700">{{ $stats['submissions']['by_status']['draft'] }}</div>
                    <div class="text-xs text-gray-600">Черновики</div>
                </div>
                <div class="bg-blue-100 p-4 rounded-lg">
                    <div class="text-xl font-bold text-blue-700">{{ $stats['submissions']['by_status']['submitted'] }}</div>
                    <div class="text-xs text-blue-600">На проверке</div>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg">
                    <div class="text-xl font-bold text-yellow-700">{{ $stats['submissions']['by_status']['needs_fix'] }}</div>
                    <div class="text-xs text-yellow-600">Доработка</div>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <div class="text-xl font-bold text-green-700">{{ $stats['submissions']['by_status']['accepted'] }}</div>
                    <div class="text-xs text-green-600">Принято</div>
                </div>
                <div class="bg-red-100 p-4 rounded-lg">
                    <div class="text-xl font-bold text-red-700">{{ $stats['submissions']['by_status']['rejected'] }}</div>
                    <div class="text-xs text-red-600">Отклонено</div>
                </div>
            </div>

            <h4 class="text-sm font-medium text-gray-700 mb-2">Работы по конкурсам</h4>
            <div class="space-y-2">
                @foreach($stats['submissions']['by_contest'] as $title => $count)
                    <div class="flex items-center">
                        <div class="w-48 text-sm text-gray-600 truncate">{{ $title }}</div>
                        <div class="flex-1">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($count / $stats['submissions']['total']) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="w-12 text-sm text-gray-600 text-right">{{ $count }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Файлы -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900">
                📎 Файлы
            </h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['attachments']['total'] }}</div>
                    <div class="text-sm text-gray-600">Всего файлов</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['attachments']['total_size_mb'] }} MB</div>
                    <div class="text-sm text-blue-700">Объем</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['attachments']['by_status']['pending'] }}</div>
                    <div class="text-sm text-yellow-700">На проверке</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['attachments']['by_status']['scanned'] }}</div>
                    <div class="text-sm text-green-700">Проверено</div>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Типы файлов</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-3 rounded text-center">
                        <div class="text-lg font-bold text-gray-700">{{ $stats['attachments']['by_type']['pdf'] }}</div>
                        <div class="text-xs text-gray-600">PDF</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded text-center">
                        <div class="text-lg font-bold text-gray-700">{{ $stats['attachments']['by_type']['zip'] }}</div>
                        <div class="text-xs text-gray-600">ZIP</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded text-center">
                        <div class="text-lg font-bold text-gray-700">{{ $stats['attachments']['by_type']['image'] }}</div>
                        <div class="text-xs text-gray-600">Изображения</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
