<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Submission;
use App\Models\Contest;

class SubmissionPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Submission $submission)
    {
        if ($user->isJury() || $user->isAdmin()) {
            return true;
        }

        return $user->id === $submission->user_id;
    }

    public function create(User $user, Contest $contest)
    {
        return $user->isParticipant() && $contest->is_active;
    }

    public function update(User $user, Submission $submission)
    {
        return $user->id === $submission->user_id && $submission->isEditable();
    }

    public function delete(User $user, Submission $submission)
    {
        return $user->id === $submission->user_id && $submission->isEditable();
    }

    public function submit(User $user, Submission $submission)
    {
        return $user->id === $submission->user_id && $submission->isEditable();
    }

    public function changeStatus(User $user, Submission $submission)
    {
        return $user->isJury();
    }
}
