<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Contest;
use App\Models\Submission;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Показать дашборд в зависимости от роли пользователя
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isJury()) {
            return $this->juryDashboard();
        }

        return $this->participantDashboard();
    }

    /**
     * Дашборд для администратора
     */
    private function adminDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_participants' => User::where('role', 'participant')->count(),
            'total_jury' => User::where('role', 'jury')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_contests' => Contest::count(),
            'active_contests' => Contest::where('is_active', true)->count(),
            'total_submissions' => Submission::count(),
            'submissions_by_status' => [
                'draft' => Submission::where('status', 'draft')->count(),
                'submitted' => Submission::where('status', 'submitted')->count(),
                'needs_fix' => Submission::where('status', 'needs_fix')->count(),
                'accepted' => Submission::where('status', 'accepted')->count(),
                'rejected' => Submission::where('status', 'rejected')->count(),
            ],
            'total_attachments' => Attachment::count(),
            'storage_used' => Attachment::sum('size'),
            'recent_users' => User::latest()->take(5)->get(),
            'recent_submissions' => Submission::with(['user', 'contest'])->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Дашборд для жюри
     */
    private function juryDashboard()
    {
        $data = [
            'total_submissions' => Submission::count(),
            'pending_review' => Submission::where('status', 'submitted')->count(),
            'needs_fix' => Submission::where('status', 'needs_fix')->count(),
            'accepted' => Submission::where('status', 'accepted')->count(),
            'rejected' => Submission::where('status', 'rejected')->count(),
            'submissions_for_review' => Submission::with(['user', 'contest', 'attachments'])
                ->where('status', 'submitted')
                ->latest()
                ->limit(10)
                ->get(),
            'recent_activity' => Submission::with(['user', 'contest'])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return view('dashboard.jury', $data);
    }

    /**
     * Дашборд для участника
     */
    private function participantDashboard()
    {
        $user = Auth::user();

        $data = [
            'my_submissions' => Submission::where('user_id', $user->id)
                ->with('contest')
                ->latest()
                ->limit(5)
                ->get(),
            'draft_count' => Submission::where('user_id', $user->id)
                ->where('status', 'draft')
                ->count(),
            'submitted_count' => Submission::where('user_id', $user->id)
                ->where('status', 'submitted')
                ->count(),
            'accepted_count' => Submission::where('user_id', $user->id)
                ->where('status', 'accepted')
                ->count(),
            'needs_fix_count' => Submission::where('user_id', $user->id)
                ->where('status', 'needs_fix')
                ->count(),
            'active_contests' => Contest::where('is_active', true)
                ->where('deadline_at', '>', now())
                ->limit(5)
                ->get(),
            'recent_notifications' => Notification::where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return view('dashboard.participant', $data);
    }
}
