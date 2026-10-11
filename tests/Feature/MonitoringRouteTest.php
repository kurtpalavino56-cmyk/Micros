<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot read the live monitoring feed', function () {
    $this->getJson('/monitoring/live')
        ->assertRedirect(route('login'));
});

test('admin can read the live monitoring feed', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/monitoring/live')
        ->assertOk()
        ->assertJsonStructure([
            'stats' => [
                'active_users',
                'events_today',
                'enrollments_today',
                'badges_today',
            ],
        ]);
});

test('admin dashboard shows supporting metrics and all quick access cards', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Total Learners')
        ->assertSee('Published Courses')
        ->assertSee('Pending Reviews')
        ->assertSee('Credentials Earned')
        ->assertSee('vs last week')
        ->assertSee('vs last month')
        ->assertDontSee('class="stat-arrow"')
        ->assertSee('Courses &amp; Badges', false)
        ->assertSee('Completion Verification')
        ->assertSee('User Management')
        ->assertSee('Certificates')
        ->assertSee('Announcements')
        ->assertSee('Audit Logs');
});

test('admin dashboard compares new learners with the previous week', function () {
    $admin = User::factory()->admin()->create();
    $weekStart = now()->startOfWeek();

    User::factory()->create(['created_at' => $weekStart->copy()->addDay()]);
    User::factory()->create(['created_at' => $weekStart->copy()->addDays(2)]);
    User::factory()->create(['created_at' => $weekStart->copy()->subDays(6)]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('+1</span> vs last week', false)
        ->assertSee('stat-trend-up');
});

test('faculty dashboard renders for a signed-in faculty member', function () {
    $faculty = User::factory()->faculty()->create();

    $this->actingAs($faculty)
        ->get('/Faculty-dashboard')
        ->assertOk();
});
