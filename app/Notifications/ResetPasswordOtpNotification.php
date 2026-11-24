<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordOtpNotification extends Notification
{
    public function __construct(
        public readonly string $otp,
        public readonly int $expireMinutes = 10,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode OTP Reset Kata Sandi – Hamzah Style Official')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pelanggan') . '! 👋')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Hamzah Style Official Anda.')
            ->line('Gunakan kode OTP 6 digit berikut untuk melanjutkan verifikasi:')
            ->line('# ' . $this->otp)
            ->line('Kode ini berlaku selama **' . $this->expireMinutes . ' menit** dan hanya dapat digunakan satu kali.')
            ->line('Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini. Akun Anda tetap aman.')
            ->salutation('Salam hangat, Tim Hamzah Style Official');
    }
}
