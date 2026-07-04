<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log de correos salientes para el panel de observabilidad (Fase 4 auditoría
 * de correos). Registra metadatos por defecto; el cuerpo HTML es opt-in
 * (config mail_logging.store_html) por RGPD. `email-logs:prune` purga por
 * retención (config mail_logging.retention_days).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();

            // Correlación entre MessageSending y MessageSent (header del mensaje).
            $table->uuid('correlation_uuid')->nullable()->index();

            // Destinatario del notifiable (cuando el correo viene de una
            // notification). Sin FK dura: puede ser User u otro modelo.
            $table->nullableMorphs('notifiable');

            // Clase de la notification (null si es un Mailable plano).
            $table->string('notification_class')->nullable();
            $table->string('mailer', 40)->nullable();

            $table->string('recipient_email')->index();
            $table->string('recipient_name')->nullable();
            $table->string('subject', 500)->nullable();

            // queued | sending | sent | failed
            $table->string('status', 20)->default('sending');

            // Message-ID de SES, para correlacionar bounces/complaints a futuro.
            $table->string('ses_message_id')->nullable()->index();
            $table->text('error_message')->nullable();

            // Cuerpo HTML: opt-in (RGPD). Off por defecto.
            $table->longText('html_body')->nullable();

            $table->json('meta')->nullable();

            $table->timestamp('sending_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
