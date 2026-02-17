@extends('layouts.app')

@section('title', 'Все работы')

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Все работы
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Просмотр всех поданных работ
            </p>
        </div>

        <div class="border-t border-gray-200">
            <!-- Фильтры -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6">
                <form action="{{ route('admin.submissions') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="w-40">
                        <select name="status" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Все статусы</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Черновик</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>На проверке</option>
                            <option value="needs_fix" {{ request('status') == 'needs_fix' ? 'selected' : '' }}>Требует доработки</option>
                            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Принято</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Отклонено</option>
                        </select>
                    </div>

                    <div class="w-64">
                        <select name="contest_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Все конкурсы</option>
                            @foreach($contests as $contest)
                                <option value="{{ $contest->id }}" {{ request('contest_id') == $contest->id ? 'selected' : '' }}>
                                    {{ $contest->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-64">
                        <select name="user_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Все участники</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Применить
                        </button>

                        @if(request()->hasAny(['status', 'contest_id', 'user_id']))
                            <a href="{{ route('admin.submissions') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                                Сбросить
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Таблица работ -->
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Автор
                        </th>
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
                            <td class="px-6 py-4">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $submission->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($submission->status === 'accepted') bg-green-100 text-green-800
                                @elseif($submission->status === 'rejected') bg-red-100 text-red-800
                                @elseif($submission->status === 'submitted') bg-blue-100 text-blue-800
                                @elseif($submission->status === 'needs_fix') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $submission->status }}
                            </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $submission->attachments->count() }}/3
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
                            <td colspan="7" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-500">Работы не найдены</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            @if($submissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $submissions->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
