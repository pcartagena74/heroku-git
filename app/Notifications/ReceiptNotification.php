<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Location;
use App\Models\Org;
use App\Models\Person;
use App\Models\RegFinance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptNotification extends Notification
{
    use Queueable;

    protected $rf;

    protected $event;

    protected $org;

    protected $person;

    protected $loc;

    protected $receipt;

    public $name;

    public $line1;

    public $line2;

    public $line3;

    public $c1;

    public $c2;

    public $action1;

    public $action2;

    public $url1;

    public $url2;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(RegFinance $rf, $receiptURL)
    {
        $this->rf = $rf;
        $this->event = Event::find($this->rf->eventID);
        $this->org = Org::find($this->event->orgID);
        $this->person = Person::find($this->rf->personID);
        $this->loc = Location::find($this->event->locationID);
        $this->receipt = $receiptURL;
        $this->name = $this->person->showDisplayName();
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
        $event = $this->event;
        $org = $this->org;
        $person = $this->person;
        $loc = $this->loc;

        $line1 = trans('messages.notifications.RegNote.line1',
            ['event' => $event->eventName, 'datetime' => $event->eventStartDate->format('n/j/Y g:i A'), 'loc' => $loc->locName]);

        $action1 = trans('messages.notifications.RegNote.action1');
        $url1 = $this->receipt;
        $line2 = trans('messages.notifications.thanks', ['org' => $org->orgName]);
        $action2 = trans('messages.notifications.RegNote.action2');
        $url2 = env('APP_URL').'/events/'.$event->slug;
        $line3 = trans('messages.notifications.RegNote.line2'); // See you...
        $c1 = 'success';
        $c2 = 'default';
        $postRegInfo = $event->postRegInfo;
        $name = $person->showDisplayName();

        return (new MailMessage)
            ->subject(trans(
                'messages.notifications.RegNote.subject',
                ['org' => $org->orgName, 'event' => $event->eventName]
            ))
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
                'name' => $person->showDisplayName(),
                'logoPath' => $this->org->logo_path(),
                'orgURL' => $this->org->org_url(),
            ]);
        // ->with($line1, $action1, $url1, $line2, $c1, $action2, $url2, $c2, $line3, $postRegInfo, $name);
        //->with(compact('line1','action1', 'url1', 'line2', 'c1', 'action2', 'url2', 'c2', 'line3', 'postRegInfo', 'name'));
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

    public function toDatabase($notifiable)
    {
        return [
            //
        ];
    }
}
