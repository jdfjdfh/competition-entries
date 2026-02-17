@extends('layouts.app')

@section('title', 'Редактирование работы')

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Редактирование работы
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Статус:
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $submission->status_color_class }}">
                    {{ $submission->status_name }}
                </span>
            </p>
        </div>

        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <form action="{{ route('submissions.update', $submission) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Название работы</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $submission->title) }}" required
                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('title') border-red-500 @enderror">
                        @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
                        <textarea name="description" id="description" rows="5" required
                                  class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('description') border-red-500 @enderror">{{ old('description', $submission->description) }}</textarea>
                        @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('submissions.show', $submission) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                        Отмена
                    </a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
