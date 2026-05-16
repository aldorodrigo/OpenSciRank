<?php

namespace App\Notifications;

use App\Models\AdminTask;
use App\Models\Product;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sprint 3.7 #44 — aviso al admin asignado cuando un editor paga el link
 * de una task que estaba awaiting_payment. La task pasa a pending y ya se
 * puede empezar el trabajo.
 *
 * Sin queue. Multiidioma vía HasLocalePreference.
 */
class TaskPaymentReceived extends Notification
{
    public function __construct(public AdminTask $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $productName = '—';
        $productId = $this->task->title_params['product_id'] ?? null;
        if ($productId) {
            $product = Product::find($productId);
            if ($product) {
                $productName = $product->getTranslationWithFallback('name');
            }
        }

        $taskUrl = url('/admin/admin-tasks/'.$this->task->id);
        $taskTitle = $this->task->renderedTitle();

        return (new MailMessage)
            ->subject(__('task_payment_received.subject', ['title' => $taskTitle]))
            ->greeting(__('task_payment_received.greeting', ['name' => $notifiable->name]))
            ->line(__('task_payment_received.intro', ['product' => $productName]))
            ->line(__('task_payment_received.task_info', ['title' => $taskTitle]))
            ->line(__('task_payment_received.next_step'))
            ->action(__('task_payment_received.cta'), $taskUrl);
    }
}
