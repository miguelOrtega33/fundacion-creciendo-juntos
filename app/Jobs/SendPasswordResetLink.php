<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Password;

final class SendPasswordResetLink implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(#[\SensitiveParameter] private readonly string $email)
    {
        $this->onConnection('database');
    }

    public function handle(): void
    {
        Password::sendResetLink(['email' => $this->email]);
    }
}
