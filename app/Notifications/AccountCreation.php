<?php

namespace App\Notifications;

use App\Event;
use App\Org;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountCreation extends Notification
{
    use Queueable;

    protected $person;
    protected $event;
    public $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person, Event $event)
    {
        $this->person = $person;
        $this->event = $event;
        $this->name = $person->showDisplayName();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $o = Org::find($this->person->defaultOrgID);
        $oname = $o->orgName;
        $ename = $this->event->eventName;

        return (new MailMessage)
            ->greeting(trans('messages.notifications.hello', ['firstName' => $this->name]))
            ->subject(trans('messages.notifications.new_reg_acct.subject', ['org' => $oname]))
            ->line(trans('messages.notifications.new_reg_acct.line1', ['ename' => $ename]))
            ->line(trans('messages.notifications.new_reg_acct.line2'))
            ->action(trans('messages.notifications.login'), env('APP_URL'))
            ->line(trans('messages.notifications.thanks', ['org' => $oname]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
