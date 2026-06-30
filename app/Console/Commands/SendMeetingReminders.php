<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Meeting;
use App\Notifications\MeetingReminderNotification;
use Illuminate\Support\Facades\Log;
use CyberDeep\LaravelAgoraTokenGenerator\Services\Agora;

class SendMeetingReminders extends Command
{
    protected $signature = 'app:send-meeting-reminders';
    protected $description = 'Send reminder notifications for meetings starting in 5 minutes';
    public function handle()
    {
        // Check for 5 minutes reminders (using IST timezone)
        $start5 = now()->timezone('Asia/Kolkata')->addMinutes(5)->second(0);
        $end5 = now()->timezone('Asia/Kolkata')->addMinutes(5)->second(59);

        $meetings5 = Meeting::where('status', 'approved')
            ->whereBetween('scheduled_at', [$start5, $end5])
            ->with(['host', 'participant'])
            ->get();

        // DEBUG: Print current time and all approved meetings
        $this->info('Current Time (UTC): ' . now());
        $this->info('Current Time (IST): ' . now()->timezone('Asia/Kolkata'));
        $allApproved = Meeting::where('status', 'approved')->get();
        $this->info('All approved meetings in DB:');
        foreach ($allApproved as $m) {
            $this->info('- ID: ' . $m->id . ', Scheduled At: ' . $m->scheduled_at);
        }

        $appId = env('AGORA_APP_ID');

        $this->info('Checking 5-min range: ' . $start5 . ' to ' . $end5);
        $this->info('Found ' . $meetings5->count() . ' meetings starting in 5 minutes.');

        foreach ($meetings5 as $meeting) {
            // Generate token for host
            $hostToken = null;
            if ($meeting->host && $appId) {
                $hostToken = Agora::make($meeting->host->id)
                    ->channel($meeting->room_id)
                    ->uId($meeting->host->id)
                    ->join(false)
                    ->audioOnly(false)
                    ->token();
            }

            // Generate token for participant
            $participantToken = null;
            if ($meeting->participant && $appId) {
                $participantToken = Agora::make($meeting->participant->id)
                    ->channel($meeting->room_id)
                    ->uId($meeting->participant->id)
                    ->join(false)
                    ->audioOnly(false)
                    ->token();
            }

            if ($meeting->host && method_exists($meeting->host, 'notify')) {
                $meeting->host->notify(new MeetingReminderNotification($meeting, 5, $hostToken, $appId));
                Log::info('5-min reminder sent to host of meeting ' . $meeting->id);
            }
            if ($meeting->participant && method_exists($meeting->participant, 'notify')) {
                $meeting->participant->notify(new MeetingReminderNotification($meeting, 5, $participantToken, $appId));
                Log::info('5-min reminder sent to participant of meeting ' . $meeting->id);
            }
        }

        // Check for 1 minute reminders (using IST timezone)
        $start1 = now()->timezone('Asia/Kolkata')->addMinutes(1)->second(0);
        $end1 = now()->timezone('Asia/Kolkata')->addMinutes(1)->second(59);

        $meetings1 = Meeting::where('status', 'approved')
            ->whereBetween('scheduled_at', [$start1, $end1])
            ->with(['host', 'participant'])
            ->get();

        $this->info('Found ' . $meetings1->count() . ' meetings starting in 1 minute.');

        foreach ($meetings1 as $meeting) {
            // Generate token for host
            $hostToken = null;
            if ($meeting->host && $appId) {
                $hostToken = Agora::make($meeting->host->id)
                    ->channel($meeting->room_id)
                    ->uId($meeting->host->id)
                    ->join(false)
                    ->audioOnly(false)
                    ->token();
            }

            // Generate token for participant
            $participantToken = null;
            if ($meeting->participant && $appId) {
                $participantToken = Agora::make($meeting->participant->id)
                    ->channel($meeting->room_id)
                    ->uId($meeting->participant->id)
                    ->join(false)
                    ->audioOnly(false)
                    ->token();
            }

            if ($meeting->host && method_exists($meeting->host, 'notify')) {
                $meeting->host->notify(new MeetingReminderNotification($meeting, 1, $hostToken, $appId));
                Log::info('1-min reminder sent to host of meeting ' . $meeting->id);
            }
            if ($meeting->participant && method_exists($meeting->participant, 'notify')) {
                $meeting->participant->notify(new MeetingReminderNotification($meeting, 1, $participantToken, $appId));
                Log::info('1-min reminder sent to participant of meeting ' . $meeting->id);
            }
        }

        return 0;
    }
}
