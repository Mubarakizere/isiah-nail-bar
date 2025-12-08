<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Review;

class NewReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('📢 New Review Submitted')
                    ->line('A new review has been submitted for ' . $this->review->service->name . '.')
                    ->line('Rating: ' . $this->review->rating . '/5')
                    ->action('View Review', url('/dashboard/admin/reviews'))
                    ->line('Thank you for staying informed.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'New review submitted for ' . $this->review->service->name,
            'review_id' => $this->review->id,
        ];
    }
}
