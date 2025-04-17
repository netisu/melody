<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\models\User;

class CustomNotification extends Notification
{
   
   use Queueable;
   
   protected $user; 

   public function __construct($object, string $message, $route)
   {
       $this->object = $object;
       $this->object->message = $message;
       $this->object->route = $route;
   }

   public function via(object $notifiable): array
   {
      return ['database'];
   }
  
   public function toArray(object $notifiable): array
   {
    return [
        'type' => 'user',
        'object' => $this->object ? null : null,
        'message' => $this->object->message,
        'route' => $route
    ];
   }
  
}
