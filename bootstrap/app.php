<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => App\Http\Middleware\CheckRole::class,
            'jury' => App\Http\Middleware\CheckJury::class,
            'admin' => App\Http\Middleware\CheckAdmin::class,
            'participant' => App\Http\Middleware\CheckParticipant::class,
            'submission.owner' => App\Http\Middleware\CheckSubmissionOwner::class,
            'submission.editable' => App\Http\Middleware\CheckSubmissionEditable::class,
            'contest.active' => App\Http\Middleware\CheckContestActive::class,
            'attachments.max' => App\Http\Middleware\CheckMaxAttachments::class,
            'attachment.owner' => App\Http\Middleware\CheckAttachmentOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
