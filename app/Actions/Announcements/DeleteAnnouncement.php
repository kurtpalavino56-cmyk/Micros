<?php

namespace App\Actions\Announcements;

use App\Models\Announcement;

class DeleteAnnouncement
{
    public function execute(Announcement $announcement): string
    {
        $title = $announcement->title;
        $announcement->delete();

        return $title;
    }
}
