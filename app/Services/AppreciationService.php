<?php

namespace App\Services;

use App\Models\Appreciation;
use App\Models\User;
use App\Notifications\NewAppreciationNotification;

class AppreciationService
{
    public function issueAppreciation(array $data, $senderId)
    {
        $appreciation = Appreciation::create([
            'recipient_id' => $data['recipient_id'],
            'sender_id' => $senderId,
            'category' => $data['category'],
            'message' => $data['message'],
        ]);

        // Send Notification
        $recipient = User::find($data['recipient_id']);
        if ($recipient) {
            $recipient->notify(new NewAppreciationNotification($appreciation));
        }

        return $appreciation;
    }

    public function getAppreciationHistory($recipientId = null)
    {
        $query = Appreciation::with(['recipient', 'sender'])->latest();
        if ($recipientId) {
            $query->where('recipient_id', $recipientId);
        }
        return $query->get();
    }
}
