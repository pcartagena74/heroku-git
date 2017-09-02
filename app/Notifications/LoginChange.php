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
    public function __construct (Person $person, $orig_email) {
        $this->person     = $person;
        $this->orig_email = $orig_email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via ($notifiable) {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail ($notifiable) {
        $o = Org::find($this->person->defaultOrgID);
        $name = $o->orgName;
        $new_email = $this->person->login;
        return (new MailMessage)
            ->subject('Your mCentric Login')
            ->line("Your mCentric login was recently changed from $this->orig_email to $new_email.")
            ->line('If you initiated this change, you can delete this email.')
            ->line('If you did not, you can change it back using the button below.')
            ->action('Undo Login Change', url('/u/'.$this->person->personID .'/'. encrypt($this->orig_email)))
            ->line("Thank you for using mCentric with $name");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray ($notifiable) {
        return [
            //
        ];
    }
}
