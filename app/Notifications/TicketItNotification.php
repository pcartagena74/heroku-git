<?php

namespace App\Notifications;

use App\Models\Ticketit\SettingOver;
use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketItNotification extends Notification
{
    use Queueable;
    private $to;
    private $notification_owner;
    private $template;
    private $data;
    private $subject;
    private $type;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($to, $notification_owner, $template, $data, $subject, $type)
    {
        $this->to                 = $to;
        $this->notification_owner = $notification_owner;
        $this->template           = $template;
        $this->data               = $data;
        $this->subject            = $subject;
        $this->type               = $type;
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
        $mail = new MailMessage;
        // $mail->to($to->email, $to->name);
        $mail->replyTo($this->notification_owner->email, $this->notification_owner->name);
        $mail->subject($this->subject);

        switch ($this->type) {
            case 'comment':
                $comment       = unserialize($this->data['comment']);
                $ticket        = unserialize($this->data['ticket']);
                $name          = $comment->user->name;
                $subject       = $ticket->subject;
                $status        = $ticket->status->name;
                $category      = $ticket->category->name;
                $comment_short = $comment->getShortContent();
                $content       = trans('ticketit::email/comment.data', [
                    'name'     => $comment->user->name,
                    'subject'  => $ticket->subject,
                    'status'   => $ticket->status->name,
                    'category' => $ticket->category->name,
                    'comment'  => $comment->getShortContent(),
                ]);
                break;
            case 'status';
                $notification_owner = unserialize($this->data['notification_owner']);
                $original_ticket    = unserialize($this->data['original_ticket']);
                $ticket             = unserialize($this->data['ticket']);

                $content = trans('ticketit::email/status.data', [
                    'name'       => $notification_owner->name,
                    'subject'    => $ticket->subject,
                    'old_status' => $original_ticket->status->name,
                    'new_status' => $ticket->status->name,
                ]);

                break;
            case 'agent';
                if ($this->template == 'ticketit::emails.assigned') {
                    $notification_owner = unserialize($this->data['notification_owner']);
                    $ticket             = unserialize($this->data['ticket']);
                    $content            = trans('ticketit::email/assigned.data', [
                        'name'     => $notification_owner->name,
                        'subject'  => $ticket->subject,
                        'status'   => $ticket->status->name,
                        'category' => $ticket->category->name,
                    ]);

                } else {
                    $notification_owner = unserialize($this->data['notification_owner']);
                    $ticket             = unserialize($this->data['ticket']);
                    $original_ticket    = unserialize($this->data['original_ticket']);
                    $content            = trans('ticketit::email/transfer.data', [
                        'name'         => $notification_owner->name,
                        'subject'      => $ticket->subject,
                        'status'       => $ticket->status->name,
                        'agent'        => $original_ticket->agent->name,
                        'old_category' => $original_ticket->category->name,
                        'new_category' => $ticket->category->name,
                    ]);

                }

                break;
        }

        return $mail->line($content)
            ->action(trans('messages.notifications.ticketit.action'),
                     route(SettingOver::grab('main_route') . '.show', $ticket->id));
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
