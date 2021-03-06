<?php

namespace App\Notifications;

use App\Org;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LoginChange extends Notification
{
    use Queueable;

    protected $person;
    protected $orig_email;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person, $orig_email)
    {
        $this->person = $person;
        $this->orig_email = $orig_email;
        $this->name = $person->firstName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $o = Org::find($this->person->defaultOrgID);
        $oname = $o->orgName;
        $new_email = $this->person->login;
        return (new MailMessage)
            ->greeting(trans('messages.notifications.hello', ['firstName' => $this->name]))
            ->subject(trans('messages.notifications.login_change.subject'))
            ->line(trans('messages.notifications.login_change.line1', ['old' => $this->orig_email, 'new' => $new_email]))
            ->line(trans('messages.notifications.login_change.line2'))
            ->line(trans('messages.notifications.login_change.line3'))
            ->action(trans('messages.notifications.login_change.action'),
                     url('/u/' . $this->person->personID . '/' . encrypt($this->orig_email)))
            ->line(trans('messages.notifications.thanks', ['org' => $oname]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
