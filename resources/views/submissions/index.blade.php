@extends('layouts.app')

@section('title', 'Работы')

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ Auth::user()->isJury() || Auth::user()->isAdmin() ? 'Все работы' : 'Мои работы' }}
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    {{ Auth::user()->isJury() || Auth::user()->isAdmin() ? 'Список всех поданных работ' : 'Список ваших работ на конкурсы' }}
                </p>
            </div>
        </div>

        <div class="border-t border-gray-200">
            <!-- Форма поиска и фильтрации -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6">
                <form action="{{ route('submissions.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Поиск по названию..."
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div class="w-40">
                        <select name="status" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Все статусы</option>
                            @foreach(['draft', 'submitted', 'needs_fix', 'accepted', 'rejected'] as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(Auth::user()->isJury() || Auth::user()->isAdmin())
                        <div class="w-64">
                            <select name="contest_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="">Все конкурсы</option>
                                @foreach(App\Models\Contest::all() as $contest)
                                    <option value="{{ $contest->id }}" {{ request('contest_id') == $contest->id ? 'selected' : '' }}>
                                        {{ $contest->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Поиск
                        </button>

                        @if(request()->hasAny(['search', 'status', 'contest_id']))
                            <a href="{{ route('submissions.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                                Сбросить
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Таблица с работами -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Название
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Конкурс
                        </th>
                        @if(Auth::user()->isJury() || Auth::user()->isAdmin())
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Автор
                            </th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Статус
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Файлы
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Дата
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Действия
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($submissions as $submission)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $submission->title }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ Str::limit($submission->description, 30) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $submission->contest->title }}
                            </td>

                            @if(Auth::user()->isJury() || Auth::user()->isAdmin())
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $submission->user->name }}
                                </td>
                            @endif

                            <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($submission->status === 'accepted') bg-green-100 text-green-800
                                @elseif($submission->status === 'rejected') bg-red-100 text-red-800
                                @elseif($submission->status === 'submitted') bg-blue-100 text-blue-800
                                @elseif($submission->status === 'needs_fix') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $submission->status_name }}
                            </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $submission->attachments->count() }}/3
                                @if($submission->attachments->where('status', 'scanned')->count() > 0)
                                    <span class="text-green-600 ml-1">
                                    ({{ $submission->attachments->where('status', 'scanned')->count() }} проверено)
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $submission->created_at->format('d.m.Y') }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('submissions.show', $submission) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Просмотр
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->isJury() || Auth::user()->isAdmin() ? '7' : '6' }}" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Работы не найдены</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Попробуйте изменить параметры поиска
                                </p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            @if($submissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $submissions->hasPages())
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        @if($submissions->previousPageUrl())
                            <a href="{{ $submissions->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Назад
                            </a>
                        @endif
                        @if($submissions->nextPageUrl())
                            <a href="{{ $submissions->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Вперед
                            </a>
                        @endif
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Показано с
                                <span class="font-medium">{{ $submissions->firstItem() }}</span>
                                по
                                <span class="font-medium">{{ $submissions->lastItem() }}</span>
                                из
                                <span class="font-medium">{{ $submissions->total() }}</span>
                                результатов
                            </p>
                        </div>
                        <div>
                            {{ $submissions->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
