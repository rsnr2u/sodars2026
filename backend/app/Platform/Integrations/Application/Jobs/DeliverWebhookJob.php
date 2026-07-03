<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Application\Jobs;

use App\Platform\Integrations\Domain\Webhooks\WebhookSubscription as SubscriptionModel;
use App\Platform\Integrations\Domain\Webhooks\WebhookDeliveryLog;
use App\Platform\Integrations\Domain\ValueObjects\WebhookPayload;
use App\Platform\Integrations\Domain\Contracts\WebhookSigner;
use App\Platform\Integrations\Domain\Contracts\WebhookTransport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $subscriptionId,
        protected string $eventType,
        protected array $payloadData,
        protected int $attempt = 1,
        protected ?string $deliveryLogId = null
    ) {}

    public function handle(
        WebhookSigner $signer,
        WebhookTransport $transport
    ): void {
        $subscription = SubscriptionModel::findOrFail($this->subscriptionId);

        $deliveryLogId = $this->deliveryLogId ?? (string) Str::uuid();

        $log = WebhookDeliveryLog::updateOrCreate(
            ['id' => $deliveryLogId],
            [
                'webhook_subscription_id' => $subscription->id,
                'event_type' => $this->eventType,
                'payload' => $this->payloadData,
                'attempt' => $this->attempt,
                'status' => 'processing',
            ]
        );

        $timestamp = time();

        $subject = (string) ($this->payloadData['id'] ?? $this->payloadData['code'] ?? 'N/A');
        $cloudEvent = WebhookPayload::create(
            '/' . explode('.', $this->eventType)[0],
            $this->eventType,
            $subject,
            $this->payloadData
        );

        $jsonPayload = $cloudEvent->toJson();

        $signature = $signer->sign($jsonPayload, $subscription->secret_token, $timestamp);

        $headers = [
            'X-SODARS-Signature' => $signature,
            'X-SODARS-Timestamp' => (string) $timestamp,
            'X-SODARS-Event' => $this->eventType,
            'X-SODARS-Delivery' => $log->id,
            'User-Agent' => 'SODARS-Webhook-Dispatcher/1.0',
        ];

        $startTime = microtime(true);

        $response = $transport->send($subscription->target_url, $jsonPayload, $headers);

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        $statusCode = $response['status'];
        $isSuccess = $statusCode >= 200 && $statusCode < 300;

        $logUpdate = [
            'request_headers' => $headers,
            'response_headers' => $response['headers'],
            'response_status' => $statusCode,
            'response_body' => substr($response['body'], 0, 1000),
            'duration_ms' => $duration,
            'error_message' => $response['error'],
        ];

        if ($isSuccess) {
            $logUpdate['status'] = 'delivered';
            $log->update($logUpdate);
        } else {
            if ($this->attempt < 3) {
                $logUpdate['status'] = 'retrying';
                $log->update($logUpdate);

                $delaySeconds = ($this->attempt === 1) ? 300 : 900;
                
                self::dispatch(
                    $this->subscriptionId,
                    $this->eventType,
                    $this->payloadData,
                    $this->attempt + 1,
                    $log->id
                )->delay(now()->addSeconds($delaySeconds));
            } else {
                $logUpdate['status'] = 'failed';
                $log->update($logUpdate);
            }
        }
    }
}
