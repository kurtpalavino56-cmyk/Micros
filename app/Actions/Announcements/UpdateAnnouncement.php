<?php

namespace App\Actions\Announcements;

use App\Models\Announcement;

class UpdateAnnouncement
{
    /**
     * @param  array{title: string, body: string, audience: array<int, string>}  $data
     */
    public function execute(Announcement $announcement, array $data): Announcement
    {
        $announcement->update([
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => array_values($data['audience']),
        ]);

        return $announcement->refresh();
    }
}
