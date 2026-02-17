<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAttachmentOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $attachment = $request->route('attachment');

        if (!$attachment) {
            abort(404, 'Файл не найден.');
        }

        $user = Auth::user();
        $submission = $attachment->submission;

        // Админ может скачивать любые файлы
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Жюри может скачивать файлы из любых работ
        if ($user->isJury()) {
            return $next($request);
        }

        // Участник может скачивать только свои файлы
        if ($user->isParticipant() && $submission->user_id === $user->id) {
            return $next($request);
        }

        abort(403, 'У вас нет доступа к этому файлу.');
    }
}
