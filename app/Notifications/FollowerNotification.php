<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\models\User;

class FollowerNotification extends Notification
{
   
   use Queueable;
   
   protected $user; 

   public function __construct(User $user)
   {
       $this->user = $user;
   }

   public function via(object $notifiable): array
   {
      return ['database'];
   }
  
   public function toArray(object $notifiable): array
   {
    return [
        'type' => 'user',
        'object' => $this->user->username,
        'message' => "has followed you",
        'route' => route('user.profile', $this->user->username)
    ];
   }
  
}
