<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

final class ResetPasswordNotificationTest extends TestCase
{
    public function test_notification_is_encrypted_queued_and_written_in_spanish(): void
    {
        config(['auth.passwords.users.expire' => 45]);

        $notification = new ResetPasswordNotification('token-prueba');
        $user = new User(['email' => 'maria@example.test']);
        $message = $notification->toMail($user);

        self::assertInstanceOf(ShouldQueue::class, $notification);
        self::assertInstanceOf(ShouldBeEncrypted::class, $notification);
        self::assertSame('Restablece tu contraseña', $message->subject);
        self::assertSame('Crear nueva contraseña', $message->actionText);
        self::assertStringContainsString('/restablecer-clave/token-prueba', $message->actionUrl);
        self::assertContains('Este enlace vencerá en 45 minutos.', $message->outroLines);
    }
}
