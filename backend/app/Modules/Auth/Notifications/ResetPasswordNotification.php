<?php

declare(strict_types=1);

namespace App\Modules\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

final class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * @param  CanResetPassword  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset your password')
            ->line('We received a request to reset your password.')
            ->action('Reset password', $this->resetUrl($notifiable))
            ->line('This link will expire in '.config('auth.passwords.users.expire', 60).' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * @param  CanResetPassword  $notifiable
     */
    protected function resetUrl($notifiable): string
    {
        $base = rtrim((string) config('app.frontend_url'), '/');

        return $base.'/auth/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
