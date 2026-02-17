<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckContestActive
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
        $contest = $request->route('contest');

        if (!$contest) {
            $contestId = $request->input('contest_id');
            if ($contestId) {
                $contest = \App\Models\Contest::find($contestId);
            }
        }

        if ($contest && !$contest->is_active) {
            return redirect()
                ->route('contests.show', $contest)
                ->with('error', 'Этот конкурс закрыт для подачи новых работ.');
        }

        return $next($request);
    }
}
