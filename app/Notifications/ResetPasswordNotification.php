<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Get the reset password notification mail message for the given URL.
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('E-REP - Password Reset Request')
            ->greeting('Hello!')
            ->line('You requested to reset your password for your E-REP account.')
            ->line('Click the button below to reset your password. This link will expire in 60 minutes.')
            ->action('Reset Password', $url)
            ->line('If you did not request a password reset, no action is required.')
            ->salutation(new HtmlString(__('Regards,').'<br>E-REP Team'));
    }
}
