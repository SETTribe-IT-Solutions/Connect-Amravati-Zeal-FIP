<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewAnnouncementNotification;
use Illuminate\Support\Facades\Notification;

class CommunicationService
{
    public function broadcastAnnouncement(array $data, $senderId)
    {
        $data['sender_id'] = $senderId;
        $announcement = Announcement::create($data);

        // Find target users
        $query = User::query()->where('status', 'Active');

        if (!empty($data['target_role']) && $data['target_role'] !== 'All Roles') {
            $query->whereHas('roles', function($q) use ($data) {
                $q->where('name', $data['target_role']);
            });
        }

        if (!empty($data['target_district_id'])) {
            $query->where('district_id', $data['target_district_id']);
        }
        if (!empty($data['target_taluka_id'])) {
            $query->where('taluka_id', $data['target_taluka_id']);
        }
        if (!empty($data['target_village_id'])) {
            $query->where('village_id', $data['target_village_id']);
        }

        $users = $query->get();

        // Queue notifications
        Notification::send($users, new NewAnnouncementNotification($announcement));

        return $announcement;
    }

    public function sendDirectMessage($senderId, $receiverId, $content)
    {
        return Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'content' => $content,
        ]);
    }
}
