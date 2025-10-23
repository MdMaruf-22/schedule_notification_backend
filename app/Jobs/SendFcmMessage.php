<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Reminder;
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Contract\Messaging as MessagingContract;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendFcmMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $reminderId;

    public function __construct(int $reminderId)
    {
        $this->reminderId = $reminderId;
    }

    public function handle(): void
    {
        $reminder = Reminder::find($this->reminderId);
        if (!$reminder || !$reminder->fcm_token) {
            return;
        }

        $title = $reminder->title;
        $body = 'Reminder: '.$reminder->title;

        $messaging = null;
        try {
            $messaging = app(MessagingContract::class);
        } catch (\Throwable $e) {
            $messaging = null;
        }

        $fcmKey = env('FCM_SERVER_KEY');

        try {
            if ($messaging instanceof MessagingContract) {
                $message = CloudMessage::withTarget('token', $reminder->fcm_token)
                    ->withNotification(FirebaseNotification::create($title, $body))
                    ->withData(['reminder_id' => (string) $reminder->id]);

                $messaging->send($message);
                $reminder->notified = true;
                $reminder->save();
                return;
            }

            if (empty($fcmKey)) {
                // nothing to do
                return;
            }

            $payload = [
                'to' => $reminder->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => ['reminder_id' => $reminder->id],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key='.$fcmKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $reminder->notified = true;
                $reminder->save();
            }
        } catch (\Exception $e) {
            // log and let the queue retry based on queue config
            logger()->error('SendFcmMessage error: '.$e->getMessage());
            throw $e;
        }
    }
}
