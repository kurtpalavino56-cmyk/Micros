<?php

namespace App\Actions\Announcements;

use App\Models\Announcement;
use App\Models\User;

class CreateAnnouncement
{
    /**
     * @param  array{title: string, body: string, audience: array<int, string>}  $data
     */
    public function execute(array $data, User $author): Announcement
    {
        return Announcement::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'audience' => array_values($data['audience']),
            'created_by' => $author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
