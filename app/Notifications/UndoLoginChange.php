<?php

namespace App\Notifications;

use App\Models\Org;
use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UndoLoginChange extends Notification
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
        $new_email = $this->person->login;

        return (new MailMessage)
            ->greeting(trans('messages.notifications.hello', ['firstName' => $this->name]))
            ->subject(trans('messages.notifications.UNDO.subject'))
            ->line(trans('messages.notifications.UNDO.line1', ['email' => $new_email]))
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
