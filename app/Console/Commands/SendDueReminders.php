<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Contract\Messaging as MessagingContract;

class SendDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send due reminders via FCM and mark them as notified';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $due = Reminder::where('notified', false)
            ->where('remind_at', '<=', $now)
            ->whereNotNull('fcm_token')
            ->get();

        if ($due->isEmpty()) {
            $this->info('No due reminders');
            return 0;
        }

        // Prefer Kreait Firebase Messaging if configured (service account), otherwise fall back to legacy FCM key
        $messaging = null;
        try {
            $messaging = app(MessagingContract::class);
        } catch (\Throwable $e) {
            // not available / not configured
            $messaging = null;
        }

        $fcmKey = env('FCM_SERVER_KEY');

        foreach ($due as $reminder) {
            try {
                // dispatch a queued job to send the message
                dispatch(new \App\Jobs\SendFcmMessage($reminder->id));
                $this->info("Dispatched job for reminder {$reminder->id}");
            } catch (\Exception $e) {
                $this->error('Error dispatching job: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
