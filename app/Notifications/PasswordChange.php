<?php

namespace App\Notifications;

use App\Models\Org;
use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChange extends Notification
{
    use Queueable;

    protected $person;

    public $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
        $this->name = $person->showDisplayName();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $o = Org::find($this->person->defaultOrgID);
        $oname = $o->orgName;

        return (new MailMessage)
            ->subject(trans('messages.notifications.PASS.subject'))
            ->greeting(trans('messages.notifications.hello', ['firstName' => $this->name]))
            ->line(trans('messages.notifications.PASS.line1'))
            ->line(trans('messages.notifications.PASS.line2'))
            ->line(trans('messages.notifications.PASS.line3'))
            ->action(trans('messages.notifications.PASS.action'), url('/password/reset?e='.$this->person->login))
            ->line(trans('messages.notifications.thanks', ['org' => $oname]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
