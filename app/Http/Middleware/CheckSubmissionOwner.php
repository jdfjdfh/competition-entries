<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubmissionOwner
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

        $user = Auth::user();

        // Админ и жюри могут просматривать любые работы
        if ($user->isAdmin() || $user->isJury()) {
            return $next($request);
        }

        // Участник может просматривать только свои работы
        if ($user->isParticipant() && $submission->user_id === $user->id) {
            return $next($request);
        }

        abort(403, 'У вас нет доступа к этой работе.');
    }
}
