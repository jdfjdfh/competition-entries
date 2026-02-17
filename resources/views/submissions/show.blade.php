@extends('layouts.app')

@section('title', $submission->title)

@section('content')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <!-- Заголовок -->
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">
                    {{ $submission->title }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Конкурс: <a href="{{ route('contests.show', $submission->contest) }}" class="text-indigo-600 hover:text-indigo-900">{{ $submission->contest->title }}</a>
                </p>
            </div>
            <div>
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $submission->status_color_class }}">
                {{ $submission->status_name }}
            </span>
            </div>
        </div>

        <!-- Информация о работе -->
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Автор</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($submission->user->name, 0, 1)) }}
                            </div>
                            <span class="ml-2">{{ $submission->user->name }}</span>
                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $submission->user->role_color_class }}">
                            {{ $submission->user->role_name }}
                        </span>
                        </div>
                    </dd>
                </div>

                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Описание</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-line">
                        {{ $submission->description }}
                    </dd>
                </div>

                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Дата создания</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $submission->created_at->format('d.m.Y H:i') }}
                        <span class="text-gray-400 text-xs ml-2">({{ $submission->created_at->diffForHumans() }})</span>
                    </dd>
                </div>

                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Последнее обновление</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $submission->updated_at->format('d.m.Y H:i') }}
                        @if($submission->updated_at != $submission->created_at)
                            <span class="text-gray-400 text-xs ml-2">({{ $submission->updated_at->diffForHumans() }})</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Статистика файлов -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center space-x-6">
                <div class="flex items-center">
                    <span class="text-sm font-medium text-gray-700 mr-2">Файлы:</span>
                    <span class="text-sm text-gray-900">{{ $attachments_stats['total'] }}/3</span>
                </div>
                @if($attachments_stats['scanned'] > 0)
                    <div class="flex items-center">
                        <span class="h-2 w-2 bg-green-500 rounded-full mr-1"></span>
                        <span class="text-xs text-gray-600">{{ $attachments_stats['scanned'] }} проверено</span>
                    </div>
                @endif
                @if($attachments_stats['pending'] > 0)
                    <div class="flex items-center">
                        <span class="h-2 w-2 bg-yellow-500 rounded-full mr-1"></span>
                        <span class="text-xs text-gray-600">{{ $attachments_stats['pending'] }} в очереди</span>
                    </div>
                @endif
                @if($attachments_stats['rejected'] > 0)
                    <div class="flex items-center">
                        <span class="h-2 w-2 bg-red-500 rounded-full mr-1"></span>
                        <span class="text-xs text-gray-600">{{ $attachments_stats['rejected'] }} отклонено</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Файлы -->
        <div class="px-4 py-5 sm:px-6 border-t border-gray-200">
            <h4 class="text-lg font-medium text-gray-900 mb-4">📎 Прикрепленные файлы</h4>

            <!-- Форма загрузки (только для автора в редактируемом статусе) -->
            @if($can_upload)
                <div class="mb-6 bg-blue-50 rounded-lg p-4">
                    <form action="{{ route('attachments.upload', $submission) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4">
                            <div class="flex-1 w-full">
                                <input type="file"
                                       name="file"
                                       id="file"
                                       accept=".pdf,.zip,.png,.jpg,.jpeg,application/pdf,application/zip,image/png,image/jpeg"
                                       onchange="validateFile(this)"
                                       required
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            <button type="submit"
                                    class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg whitespace-nowrap">
                                Загрузить файл
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Максимум 3 файла, до 10MB каждый. Разрешены только:
                            <span class="font-semibold">PDF, ZIP, PNG, JPG</span>
                        </p>
                    </form>
                </div>
            @endif

            <!-- Список файлов -->
            <div class="bg-gray-50 rounded-lg divide-y divide-gray-200">
                @forelse($submission->attachments as $attachment)
                    <div class="p-4 flex items-center justify-between hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3 flex-1">
                            <!-- Иконка в зависимости от типа -->
                            <div class="flex-shrink-0">
                                @if(strpos($attachment->mime, 'pdf') !== false)
                                    <div class="h-10 w-10 bg-red-100 rounded-lg flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-sm">PDF</span>
                                    </div>
                                @elseif(strpos($attachment->mime, 'zip') !== false)
                                    <div class="h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <span class="text-yellow-600 font-bold text-sm">ZIP</span>
                                    </div>
                                @elseif(strpos($attachment->mime, 'image') !== false)
                                    <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <span class="text-green-600 font-bold text-sm">IMG</span>
                                    </div>
                                @else
                                    <div class="h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-600 font-bold text-sm">FILE</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Информация о файле -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $attachment->original_name }}
                                </p>
                                <div class="flex items-center space-x-2 text-xs">
                                    <span class="text-gray-500">{{ round($attachment->size / 1024, 2) }} KB</span>
                                    <span class="text-gray-300">•</span>
                                    <span class="
                                    @if($attachment->status === 'scanned') text-green-600
                                    @elseif($attachment->status === 'rejected') text-red-600
                                    @else text-yellow-600 @endif
                                ">
                                    @if($attachment->status === 'scanned')
                                            ✅ Проверен
                                        @elseif($attachment->status === 'rejected')
                                            ❌ Отклонен
                                        @else
                                            ⏳ В очереди на проверку
                                        @endif
                                </span>
                                    @if($attachment->rejection_reason)
                                        <span class="text-red-600">({{ $attachment->rejection_reason }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Действия с файлом -->
                        <div class="flex items-center space-x-2 ml-4">
                            @if($attachment->status === 'scanned' || $attachment->status === 'pending')
                                <a href="{{ route('attachments.download', [$submission, $attachment]) }}"
                                   class="text-indigo-600 hover:text-indigo-900 p-2 hover:bg-indigo-50 rounded-lg transition-colors"
                                   title="Скачать">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </a>
                            @endif

                            @if($can_edit && $attachment->user_id === Auth::id())
                                <form action="{{ route('attachments.destroy', [$submission, $attachment]) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот файл?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Удалить">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Нет файлов</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($can_upload)
                                Загрузите файлы, чтобы отправить работу на конкурс
                            @else
                                К этой работе еще не прикреплены файлы
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Комментарии -->
        <div class="px-4 py-5 sm:px-6 border-t border-gray-200">
            <h4 class="text-lg font-medium text-gray-900 mb-4">💬 Комментарии</h4>

            <!-- Список комментариев -->
            <div class="space-y-4 mb-6">
                @forelse($submission->comments as $comment)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center space-x-2">
                                <div class="h-6 w-6 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-xs">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                    <span class="text-xs text-gray-500 ml-2">{{ $comment->created_at->format('d.m.Y H:i') }}</span>
                                </div>
                                @if($comment->user->isJury())
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-xs rounded-full">Жюри</span>
                                @endif
                                @if($comment->user->isAdmin())
                                    <span class="px-2 py-0.5 bg-red-100 text-red-800 text-xs rounded-full">Админ</span>
                                @endif
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $comment->body }}</p>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">Пока нет комментариев</p>
                    </div>
                @endforelse
            </div>

            <!-- Форма добавления комментария -->
            <form action="{{ route('comments.store', $submission) }}" method="POST" class="mt-4">
                @csrf
                <div>
                    <label for="comment" class="sr-only">Комментарий</label>
                    <textarea name="body"
                              id="comment"
                              rows="3"
                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                              placeholder="Напишите комментарий..."></textarea>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Отправить комментарий
                    </button>
                </div>
            </form>
        </div>

        <!-- Действия для участника -->
        @if(Auth::id() === $submission->user_id)
            <div class="px-4 py-5 sm:px-6 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                    @if($can_edit)
                        <a href="{{ route('submissions.edit', $submission) }}"
                           class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Редактировать
                        </a>
                    @endif

                    @if($can_submit)
                        <form action="{{ route('submissions.submit', $submission) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Отправить работу на проверку? После отправки редактирование будет недоступно.')"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Отправить на проверку
                            </button>
                        </form>
                    @endif

                    @if($submission->status === 'needs_fix')
                        <div class="flex items-center text-yellow-700 bg-yellow-50 px-4 py-2 rounded-md">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span class="text-sm">Требуется доработка. Ознакомьтесь с комментариями жюри.</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Действия для жюри -->
        @if(Auth::user()->isJury() || Auth::user()->isAdmin())
            @if($submission->status === 'submitted')
                <div class="px-4 py-5 sm:px-6 border-t border-gray-200 bg-purple-50">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">⚖️ Действия жюри</h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Принять -->
                        <form action="{{ route('submissions.change-status', $submission) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit"
                                    onclick="return confirm('✅ Принять эту работу?\n\nУчастник получит уведомление о победе.')"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                                <span>✅</span>
                                <span>Принять работу</span>
                            </button>
                        </form>

                        <!-- Отклонить -->
                        <form action="{{ route('submissions.change-status', $submission) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit"
                                    onclick="return confirm('❌ Отклонить эту работу?\n\nУчастник получит уведомление об отказе.')"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                                <span>❌</span>
                                <span>Отклонить</span>
                            </button>
                        </form>

                        <!-- Запросить доработку -->
                        <button onclick="showNeedsFixModal()"
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                            <span>📝</span>
                            <span>Запросить доработку</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Для работ, которые уже на доработке -->
            @if($submission->status === 'needs_fix')
                <div class="px-4 py-5 sm:px-6 border-t border-gray-200 bg-yellow-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-3xl">⏳</span>
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">Работа на доработке</h4>
                                <p class="text-sm text-gray-600">Участник уже получил комментарии и работает над исправлениями</p>
                            </div>
                        </div>

                        <form action="{{ route('submissions.change-status', $submission) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit"
                                    onclick="return confirm('❌ Отклонить эту работу окончательно?')"
                                    class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                                Отклонить окончательно
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Модальное окно для комментария к доработке -->
            <div id="needsFixModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">📝 Запрос на доработку</h3>

                    <form action="{{ route('submissions.change-status', $submission) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="needs_fix">

                        <div class="mb-4">
                            <label for="jury_comment" class="block text-sm font-medium text-gray-700 mb-2">
                                Комментарий к доработке <span class="text-red-500">*</span>
                            </label>
                            <textarea name="comment"
                                      id="jury_comment"
                                      rows="4"
                                      required
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                                      placeholder="Укажите, что именно нужно исправить..."></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Этот комментарий увидит участник
                            </p>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button"
                                    onclick="hideNeedsFixModal()"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                Отмена
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">
                                Отправить запрос
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        function validateFile(input) {
            const file = input.files[0];
            if (!file) return;

            const allowedTypes = [
                'application/pdf',
                'application/zip',
                'application/x-zip-compressed',
                'image/png',
                'image/jpeg',
                'image/jpg'
            ];

            const allowedExtensions = ['pdf', 'zip', 'png', 'jpg', 'jpeg'];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!allowedTypes.includes(file.type) || !allowedExtensions.includes(extension)) {
                alert('❌ Ошибка: Разрешены только файлы форматов PDF, ZIP, PNG, JPG');
                input.value = '';
                return;
            }

            if (file.size > 10485760) {
                alert('❌ Ошибка: Размер файла не должен превышать 10MB');
                input.value = '';
                return;
            }
        }

        function showNeedsFixModal() {
            const modal = document.getElementById('needsFixModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function hideNeedsFixModal() {
            const modal = document.getElementById('needsFixModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // Закрытие по клику вне модалки
        document.getElementById('needsFixModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                hideNeedsFixModal();
            }
        });

        // Закрытие по ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideNeedsFixModal();
            }
        });
    </script>

    <style>
        /* Анимация для модального окна */
        #needsFixModal {
            transition: opacity 0.2s ease;
        }
    </style>
@endsection
