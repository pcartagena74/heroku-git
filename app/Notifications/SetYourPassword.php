<?php

namespace App\Notifications;

use App\Org;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SetYourPassword extends Notification
{
    use Queueable;

    protected $person;
    protected $o;
    public $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
        $this->o = Org::find($this->person->defaultOrgID);
        $this->name = $person->showDisplayName();
    }


    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $oname = $this->o->orgName;
        return (new MailMessage)
            ->greeting(trans('messages.notifications.hello', ['firstName' => $this->name]))
            ->subject(trans('messages.notifications.SYP.subject'))
            ->line(trans('messages.notifications.SYP.line1', ['name' => $oname]))
            ->line(trans('messages.notifications.SYP.line1'))
            ->line(trans('messages.notifications.SYP.line2'))
            ->action(trans('messages.notifications.SYP.action'), url('/password/reset?e='.$this->person->login))
            ->line(trans('messages.notifications.thanks', ['org' => $oname]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
