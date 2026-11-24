<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Permintaan Reset Kata Sandi – Hamzah Style Official')
            ->greeting('Halo, '.$notifiable->name.'! 👋')
            ->line('Kami menerima permintaan untuk mereset kata sandi akun Hamzah Style Official Anda.')
            ->line('Klik tombol di bawah ini untuk membuat kata sandi baru Anda:')
            ->action('Reset Kata Sandi Sekarang', $resetUrl)
            ->line('Tautan reset kata sandi ini akan kedaluwarsa dalam '.(int) config('auth.passwords.users.expire', 60).' menit.')
            ->line('Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini. Akun Anda tetap aman.')
            ->salutation('Salam hangat, Tim Hamzah Style Official');
    }
}

