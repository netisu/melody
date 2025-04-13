<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;
use App\Models\Item;

class GiftNotification extends Notification
{
   
   use Queueable;
   
   protected $user; 
   protected $item; 


   public function __construct(User $user, Item $item)
   {
       $this->user = $user;
       $this->item = $item;

   }

   public function via(object $notifiable): array
   {
      return ['database'];
   }
  
   public function toArray(object $notifiable): array
   {
    return [
        'type' => 'gift',
        'object' => $this->user->username,
        'message' => "has gifted you " . $this->item->name,
        'route' => route('store.item', $this->item->id)
    ];
   }
  
}
