@extends('layouts.app')

@section('title', $submission->title)

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ $submission->title }}
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Конкурс: {{ $submission->contest->title }}
                </p>
            </div>
            <div>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $submission->status_color_class }}">
                    {{ $submission->status_name }}
                </span>
            </div>
        </div>

        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Автор</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $submission->user->name }}
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $submission->user->role_color_class }}">
                            {{ $submission->user->role_name }}
                        </span>
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Описание</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $submission->description }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Дата создания</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $submission->created_at->format('d.m.Y H:i') }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Файлы -->
        <div class="px-4 py-5 sm:px-6 border-t border-gray-200">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Прикрепленные файлы</h4>

            @if($submission->isEditable() && $submission->attachments->count() < 3)
                <div class="mb-4">
                    <form action="{{ route('attachments.upload', $submission) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="flex items-center space-x-4">
                            <input type="file"
                                   name="file"
                                   id="file"
                                   accept=".pdf,.zip,.png,.jpg,.jpeg,application/pdf,application/zip,image/png,image/jpeg"
                                   onchange="checkFileType(this)"
                                   required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                Загрузить файл
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Максимум 3 файла, до 10MB каждый. Разрешены: PDF, ZIP, PNG, JPG</p>
                    </form>
                </div>
            @endif

            <div class="bg-gray-50 rounded-lg p-4">
                @forelse($submission->attachments as $attachment)
                    <div class="flex items-center justify-between py-2 border-b last:border-0">
                        <div class="flex items-center space-x-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $attachment->original_name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ round($attachment->size / 1024, 2) }} KB •
                                    <span class="
                                    @if($attachment->status === 'scanned') text-green-600
                                    @elseif($attachment->status === 'rejected') text-red-600
                                    @else text-yellow-600 @endif
                                    ">
                                        @if($attachment->status === 'scanned') Проверен
                                        @elseif($attachment->status === 'rejected') Отклонен
                                        @else В ожидании
                                        @endif
                                    </span>
                                    @if($attachment->rejection_reason)
                                        <span class="text-red-600"> ({{ $attachment->rejection_reason }})</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('attachments.download', [$submission, $attachment]) }}"
                               class="text-indigo-600 hover:text-indigo-900 text-sm">
                                Скачать
                            </a>
                            @if($submission->isEditable() && $attachment->user_id === Auth::id())
                                <form action="{{ route('attachments.destroy', [$submission, $attachment]) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить этот файл?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                                        Удалить
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Нет прикрепленных файлов</p>
                @endforelse
            </div>
        </div>

        <!-- Комментарии -->
        <div class="px-4 py-5 sm:px-6 border-t border-gray-200">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Комментарии</h4>

            <div class="space-y-4 mb-4">
                @forelse($submission->comments as $comment)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $comment->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                            @if($comment->user_id === Auth::id())
                                <span class="text-xs text-gray-500">Ваш комментарий</span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-gray-700">{{ $comment->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Нет комментариев</p>
                @endforelse
            </div>

            <form action="{{ route('comments.store', $submission) }}" method="POST" class="mt-4">
                @csrf
                <div>
                    <label for="comment" class="sr-only">Комментарий</label>
                    <textarea name="body" id="comment" rows="3"
                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                              placeholder="Напишите комментарий..."></textarea>
                </div>
                <div class="mt-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Отправить
                    </button>
                </div>
            </form>
        </div>

        <!-- Действия -->
        @if(Auth::user()->isJury() && $submission->status === 'submitted')
            <div class="px-4 py-5 sm:px-6 border-t border-gray-200">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Действия жюри</h4>
                <div class="flex space-x-4">
                    <form action="{{ route('submissions.change-status', $submission) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Принять эту работу?')">
                            Принять
                        </button>
                    </form>
                    <form action="{{ route('submissions.change-status', $submission) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Отклонить эту работу?')">
                            Отклонить
                        </button>
                    </form>
                    <button onclick="showNeedsFixForm()" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Запросить доработку
                    </button>
                </div>

                <div id="needsFixForm" class="hidden mt-4">
                    <form action="{{ route('submissions.change-status', $submission) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="needs_fix">
                        <div>
                            <label for="fix_comment" class="block text-sm font-medium text-gray-700">Комментарий к доработке</label>
                            <textarea name="comment" id="fix_comment" rows="3"
                                      class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                      required></textarea>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                Отправить запрос
                            </button>
                            <button type="button" onclick="hideNeedsFixForm()" class="ml-2 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($submission->isEditable() && Auth::id() === $submission->user_id)
            <div class="px-4 py-5 sm:px-6 border-t border-gray-200">
                <div class="flex space-x-4">
                    <a href="{{ route('submissions.edit', $submission) }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Редактировать
                    </a>
                    @if($submission->hasScannedAttachments())
                        <form action="{{ route('submissions.submit', $submission) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Отправить работу на конкурс?')">
                                Отправить на конкурс
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        function showNeedsFixForm() {
            document.getElementById('needsFixForm').classList.remove('hidden');
        }

        function hideNeedsFixForm() {
            document.getElementById('needsFixForm').classList.add('hidden');
        }
    </script>
@endsection
