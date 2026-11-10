<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetPasswordNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(#[\SensitiveParameter] private readonly string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expirationMinutes = (int) config('auth.passwords.users.expire', 60);
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Restablece tu contraseña')
            ->greeting('Hola')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Crear nueva contraseña', $url)
            ->line("Este enlace vencerá en {$expirationMinutes} minutos.")
            ->line('Si no solicitaste este cambio, puedes ignorar este correo.');
    }
}
