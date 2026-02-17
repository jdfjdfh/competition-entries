<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubmissionEditable
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

        $submission = $request->route('submission');

        if (!$submission) {
            abort(404, 'Работа не найдена.');
        }

        // Проверяем, может ли пользователь редактировать работу
        if (Auth::user()->id !== $submission->user_id) {
            abort(403, 'Вы не являетесь автором этой работы.');
        }

        if (!$submission->isEditable()) {
            return redirect()
                ->route('submissions.show', $submission)
                ->with('error', 'Работу нельзя редактировать в текущем статусе.');
        }

        return $next($request);
    }
}
