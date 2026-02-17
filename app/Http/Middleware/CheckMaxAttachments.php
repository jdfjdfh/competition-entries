<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMaxAttachments
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
        $submission = $request->route('submission');

        if ($submission && $submission->attachments()->count() >= 3) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Достигнуто максимальное количество файлов (3)'
                ], 400);
            }

            return redirect()
                ->route('submissions.show', $submission)
                ->with('error', 'Достигнуто максимальное количество файлов (3)');
        }

        return $next($request);
    }
}
