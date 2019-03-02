<?php

namespace App\Notifications;

use App\RegFinance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Event;
use App\Org;
use App\Person;
use App\Location;

class ReceiptNotification extends Notification
{
    use Queueable;

    protected $rf;
    protected $event;
    protected $org;
    protected $person;
    protected $loc;
    protected $receipt;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(RegFinance $rf, $receiptURL)
    {
        $this->rf = $rf;
        $this->event  = Event::find($this->rf->eventID);
        $this->org    = Org::find($this->event->orgID);
        $this->person = Person::find($this->rf->personID);
        $this->loc    = Location::find($this->event->locationID);
        $this->receipt = $receiptURL;
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
        $event  = $this->event;
        $org    = $this->org;
        $person = $this->person;
        $loc    = $this->loc;

        $line1 = trans('messages.notifications.RegNote.line1',
            ['event' => $event->eventName,
             'datetime' => $event->eventStartDate->format('n/j/Y g:i A'),
             'loc' => $loc->locName ]);

        $action1 = trans('messages.notifications.RegNote.action1');
        $url1 = $this->receipt;
        $line2 = trans('messages.notifications.thanks', ['org' => $org->orgName]);
        $action2 = trans('messages.notifications.RegNote.action2');
        $url2 = env('APP_URL')."/events/".$event->eventID;
        $line3 = trans('messages.notifications.RegNote.line2');

        return (new MailMessage)
            ->subject(trans('messages.notifications.RegNote.subject', ['org' => $org->orgName]))
            ->markdown('notifications.two_button_note', [
                'line1' => $line1,
                'action1' => $action1,
                'url1' => $url1,
                'line2' => $line2,
                'c1' => 'success',
                'action2' => $action2,
                'url2' => $url2,
                'c2' => 'default',
                'line3' => $line3,
                'postRegInfo' => $event->postRegInfo,
                'name' => $person->showDisplayName()
            ]);
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

    public function toDatabase($notifiable){
        return [
            //
        ];
    }
}
