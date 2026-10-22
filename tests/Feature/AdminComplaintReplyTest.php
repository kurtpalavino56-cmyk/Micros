<?php

use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('admin inbox uses rich text only for visitor replies', function () {
    $admin = User::factory()->admin()->create();
    $faculty = User::factory()->faculty()->create();
    $facultyThread = Complaint::create([
        'user_id' => $faculty->id,
        'source' => 'faculty',
        'subject' => 'Faculty question',
        'message' => 'Need assistance.',
        'status' => 'open',
    ]);
    $visitorThread = Complaint::create([
        'source' => 'visitor',
        'guest_name' => 'Site visitor',
        'guest_email' => 'visitor@example.test',
        'subject' => 'Visitor question',
        'message' => 'Need information.',
        'status' => 'open',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.complaints', ['thread' => $facultyThread->id]))
        ->assertOk()
        ->assertDontSee('data-ckeditor')
        ->assertSee('name="attachment"', false)
        ->assertSee('reply-attachment-picker', false)
        ->assertSee('<textarea name="body" rows="1"', false)
        ->assertSee('detail-hd-top', false)
        ->assertSee('.msg.from-admin > div{margin-right:0;align-items:flex-end;}', false)
        ->assertSee('.msg.from-admin .bubble{align-self:flex-end;}', false)
        ->assertSee('Mark as resolved');

    $this->actingAs($admin)
        ->get(route('admin.complaints', ['thread' => $visitorThread->id]))
        ->assertOk()
        ->assertSee('data-ckeditor')
        ->assertSee('Send Reply')
        ->assertSee('Mark as resolved')
        ->assertDontSee('name="attachment"', false);
});

test('admin can send a plain text faculty reply with an image attachment', function () {
    $admin = User::factory()->admin()->create();
    $faculty = User::factory()->faculty()->create();
    $thread = Complaint::create([
        'user_id' => $faculty->id,
        'source' => 'faculty',
        'subject' => 'Faculty question',
        'message' => 'Need assistance.',
        'status' => 'open',
    ]);
    $image = UploadedFile::fake()->image('screenshot.png');

    $this->actingAs($admin)
        ->post(route('admin.complaints.reply', $thread->id), [
            'body' => 'Please see the screenshot.',
            'attachment' => $image,
        ])
        ->assertRedirect(route('admin.complaints', ['thread' => $thread->id]));

    $reply = ComplaintReply::where('complaint_id', $thread->id)->firstOrFail();

    expect($reply->body)->toBe('Please see the screenshot.')
        ->and($reply->attachment_name)->toBe('screenshot.png')
        ->and(is_file(public_path($reply->attachment_url)))->toBeTrue();

    $this->actingAs($faculty)
        ->get(route('complaint-replies.attachment', $reply->id))
        ->assertOk();

    $otherUser = User::factory()->create();
    $this->actingAs($otherUser)
        ->get(route('complaint-replies.attachment', $reply->id))
        ->assertForbidden();

    $this->actingAs($faculty)
        ->get(route('faculty.inbox', ['thread' => $thread->id]))
        ->assertOk()
        ->assertSee(route('complaint-replies.attachment', $reply->id), false);

    unlink(public_path($reply->attachment_url));
});

test('student and faculty inboxes can send replies with image attachments', function () {
    foreach ([
        ['user' => User::factory()->create(), 'source' => 'student', 'route' => 'inbox.index', 'replyRoute' => 'inbox.reply'],
        ['user' => User::factory()->faculty()->create(), 'source' => 'faculty', 'route' => 'faculty.inbox', 'replyRoute' => 'faculty.inbox.reply'],
    ] as $case) {
        $thread = Complaint::create([
            'user_id' => $case['user']->id,
            'source' => $case['source'],
            'subject' => ucfirst($case['source']).' question',
            'message' => 'Need assistance.',
            'status' => 'open',
        ]);

        $this->actingAs($case['user'])
            ->get(route($case['route'], ['thread' => $thread->id]))
            ->assertOk()
            ->assertSee('reply-field-compose', false)
            ->assertSee('reply-attachment-picker', false)
            ->assertSee('name="attachment"', false)
            ->assertSee('rows="1"', false);

        $this->actingAs($case['user'])
            ->post(route($case['replyRoute'], $thread->id), [
                'body' => 'Here is an image.',
                'attachment' => UploadedFile::fake()->image('reply.png'),
            ])
            ->assertRedirect(route($case['route'], ['thread' => $thread->id]));

        $reply = ComplaintReply::where('complaint_id', $thread->id)->firstOrFail();

        expect($reply->body)->toBe('Here is an image.')
            ->and($reply->attachment_name)->toBe('reply.png')
            ->and(is_file(public_path($reply->attachment_url)))->toBeTrue();

        $this->actingAs($case['user'])
            ->get(route('complaint-replies.attachment', $reply->id))
            ->assertOk();

        unlink(public_path($reply->attachment_url));
    }
});
