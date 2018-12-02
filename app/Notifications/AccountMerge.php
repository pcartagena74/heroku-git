<?php

namespace App\Notifications;

use App\Event;
use App\Org;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AccountMerge extends Notification
{
    use Queueable;

    protected $person1;
    protected $person2;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person1, Person $person2)
    {
        $this->person1 = $person1;
        $this->person2 = $person2;
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
        $name = $o->orgName;

        return (new MailMessage)
            ->subject(trans('messages.messages.merge_sub', ['name' => $name]))
            ->line(trans('messages.messages.merge_msg1', ['orgname' => $name]))
            ->line(trans('messages.messages.merge_msg2', ['email1' => $this->person1->login, 'email2' => $this->person2->login]))
            ->action(trans('messages.messages.visit_mCentric'), env('APP_URL'))
            ->line(trans('messages.messages.thanks', ['orgname' => $name]));
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
