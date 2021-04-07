<?php

namespace App\Notifications;

use App\Event;
use App\Org;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountMerge extends Notification
{
    use Queueable;

    protected $person1;
    protected $person2;
    public $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person1, Person $person2)
    {
        $this->person1 = $person1;
        $this->person2 = $person2;
        $this->name = $person1->showDisplayName();
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
        $o = Org::find($this->person2->defaultOrgID);
        $oname = $o->orgName;

        return (new MailMessage)
            ->greeting(trans('messages.notifications.hello', ['firstName' => $this->name]))
            ->subject(trans('messages.messages.merge_sub', ['name' => $oname]))
            ->line(trans('messages.messages.merge_msg1', ['orgname' => $oname]))
            ->line(trans('messages.messages.merge_msg2', ['email1' => $this->person1->login, 'email2' => $this->person2->login]))
            ->action(trans('messages.messages.visit_mCentric'), env('APP_URL'))
            ->line(trans('messages.messages.thanks', ['orgname' => $oname]));
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
