<?php

namespace App\Notifications;

use App\Models\AdminTask;
use App\Models\Product;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sprint 3.7 #44 Fase 2 — aviso al editor cuando una task se cierra y había
 * un link de pago pendiente. Le decimos que ya no necesita pagar.
 *
 * Sin queue (QUEUE_CONNECTION=sync). Multiidioma vía HasLocalePreference.
 */
class TaskCompletedPaymentCancelled extends QueuedNotification
{
    public function __construct(public AdminTask $task) {}

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

        $threadUrl = $this->task->source_message_id
            ? url('/app/messages/'.optional($this->task->sourceMessage)->conversation_id)
            : url('/app/messages');

        return (new MailMessage)
            ->subject(__('task_completed_payment_cancelled.subject'))
            ->greeting(__('task_completed_payment_cancelled.greeting', ['name' => $notifiable->name]))
            ->line(__('task_completed_payment_cancelled.intro', ['product' => $productName]))
            ->line(__('task_completed_payment_cancelled.explanation'))
            ->action(__('task_completed_payment_cancelled.cta'), $threadUrl)
            ->line(__('task_completed_payment_cancelled.outro'));
    }
}
