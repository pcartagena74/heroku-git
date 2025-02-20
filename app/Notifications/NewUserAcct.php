<?php

namespace App\Notifications;

use App\Models\Org;
use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserAcct extends Notification
{
    use Queueable;

    protected $person;

    protected $pass;

    protected $creator;

    public $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person, $pass, $creator)
    {
        $this->person = $person;
        $this->pass = $pass;
        $this->creator = Person::find($creator);
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
            ->subject(trans('messages.notifications.new_user_acct.subject', ['org' => $oname]))
            ->greeting(trans('messages.notifications.hello', ['firstName' => $this->name]))
            ->line(trans('messages.notifications.new_user_acct.line1', ['name' => $this->creator->showFullName(), 'org' => $oname]))
            ->line(trans('messages.notifications.new_user_acct.line2'))
            ->line(trans('messages.notifications.new_user_acct.line3', ['pass' => $this->pass]))
            ->action(trans('messages.notifications.login'), env('APP_URL'))
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
