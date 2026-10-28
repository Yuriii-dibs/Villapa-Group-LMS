<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassAttendance;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Event;
use App\Models\Message;
use App\Models\Role;
use App\Models\User;
use App\Services\CalendarService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatCalendarAndOnlineClassesTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructor1;
    protected User $instructor2;
    protected User $student1;
    protected User $student2;
    protected User $admin;
    protected Course $course1;
    protected Course $course2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $category = Category::factory()->create();

        $this->instructor1 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $this->instructor1->assignRole(Role::INSTRUCTOR);

        $this->instructor2 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $this->instructor2->assignRole(Role::INSTRUCTOR);

        $this->student1 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $this->student1->assignRole(Role::STUDENT);

        $this->student2 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $this->student2->assignRole(Role::STUDENT);

        $this->admin = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $this->admin->assignRole(Role::ADMIN);

        // Course 1 taught by Instructor 1, Student 1 is enrolled
        $this->course1 = Course::factory()->create([
            'instructor_id' => $this->instructor1->id,
            'category_id' => $category->id,
            'publication_status' => Course::PUB_PUBLISHED,
            'approval_status' => Course::APPROVAL_APPROVED,
        ]);
        CourseEnrollment::create([
            'user_id' => $this->student1->id,
            'course_id' => $this->course1->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now(),
        ]);

        // Course 2 taught by Instructor 2, Student 2 is enrolled
        $this->course2 = Course::factory()->create([
            'instructor_id' => $this->instructor2->id,
            'category_id' => $category->id,
            'publication_status' => Course::PUB_PUBLISHED,
            'approval_status' => Course::APPROVAL_APPROVED,
        ]);
        CourseEnrollment::create([
            'user_id' => $this->student2->id,
            'course_id' => $this->course2->id,
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now(),
        ]);
    }

    public function test_non_enrolled_student_cannot_read_or_send_course_chat(): void
    {
        // Student 2 is NOT enrolled in Course 1
        // Attempt to read -> 403 Forbidden
        $readResponse = $this->actingAs($this->student2)
            ->getJson("/chat/courses/{$this->course1->id}");
        $readResponse->assertStatus(403);

        // Attempt to send -> 403 Forbidden
        $sendResponse = $this->actingAs($this->student2)
            ->postJson("/chat/courses/{$this->course1->id}", [
                'body' => 'I should not be able to post here.',
            ]);
        $sendResponse->assertStatus(403);

        // Enrolled Student 1 CAN read and send
        $validSend = $this->actingAs($this->student1)
            ->postJson("/chat/courses/{$this->course1->id}", [
                'body' => 'Hello classmates!',
            ]);
        $validSend->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'course_id' => $this->course1->id,
            'sender_id' => $this->student1->id,
            'body' => 'Hello classmates!',
        ]);

        $validRead = $this->actingAs($this->student1)
            ->getJson("/chat/courses/{$this->course1->id}");
        $validRead->assertStatus(200);
        $validRead->assertJsonFragment(['body' => 'Hello classmates!']);

        // Course instructor CAN read and send
        $instructorSend = $this->actingAs($this->instructor1)
            ->postJson("/chat/courses/{$this->course1->id}", [
                'body' => 'Welcome to the course discussion.',
            ]);
        $instructorSend->assertStatus(201);

        // Admin CAN read
        $adminRead = $this->actingAs($this->admin)
            ->getJson("/chat/courses/{$this->course1->id}");
        $adminRead->assertStatus(200);
    }

    public function test_student_cannot_message_an_unrelated_instructor(): void
    {
        // Student 1 shares Course 1 with Instructor 1, but NOT with Instructor 2
        // Attempting to message Instructor 2 -> 403 Forbidden
        $readResponse = $this->actingAs($this->student1)
            ->getJson("/chat/direct/{$this->instructor2->id}");
        $readResponse->assertStatus(403);

        $sendResponse = $this->actingAs($this->student1)
            ->postJson("/chat/direct/{$this->instructor2->id}", [
                'body' => 'Unsolicited message.',
            ]);
        $sendResponse->assertStatus(403);

        // Student 1 CAN message Instructor 1 (shared course)
        $validDirect = $this->actingAs($this->student1)
            ->postJson("/chat/direct/{$this->instructor1->id}", [
                'body' => 'Hello professor!',
            ]);
        $validDirect->assertStatus(201);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->student1->id,
            'recipient_id' => $this->instructor1->id,
            'body' => 'Hello professor!',
        ]);
    }

    public function test_messages_are_escaped_on_output(): void
    {
        $payload = '<script>alert("XSS")</script>&"test"';

        $this->actingAs($this->student1)
            ->postJson("/chat/courses/{$this->course1->id}", [
                'body' => $payload,
            ])
            ->assertStatus(201);

        $response = $this->actingAs($this->student1)
            ->getJson("/chat/courses/{$this->course1->id}");

        $response->assertStatus(200);
        $escaped = e($payload);
        $response->assertJsonFragment(['body' => $escaped]);
        // The raw script tag must NOT exist in the serialized body value
        $this->assertStringNotContainsString('<script>', $response->json('messages.0.body'));
    }

    public function test_calendar_only_shows_items_from_users_own_courses(): void
    {
        $calendarService = app(CalendarService::class);

        // Event for Course 1
        $eventC1 = Event::create([
            'title' => 'Course 1 Final Review',
            'type' => Event::TYPE_EXAM,
            'starts_at' => now()->addDays(2),
            'course_id' => $this->course1->id,
            'created_by' => $this->instructor1->id,
        ]);

        // Event for Course 2 (Student 1 is NOT enrolled)
        $eventC2 = Event::create([
            'title' => 'Course 2 Secret Meeting',
            'type' => Event::TYPE_CLASS,
            'starts_at' => now()->addDays(3),
            'course_id' => $this->course2->id,
            'created_by' => $this->instructor2->id,
        ]);

        // Online Class Session for Course 1
        $sessionC1 = ClassSession::create([
            'course_id' => $this->course1->id,
            'title' => 'Live Lab Course 1',
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(1)->addHour(),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'room1-code',
            'status' => ClassSession::STATUS_SCHEDULED,
            'created_by' => $this->instructor1->id,
        ]);

        // Online Class Session for Course 2
        $sessionC2 = ClassSession::create([
            'course_id' => $this->course2->id,
            'title' => 'Live Lab Course 2',
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(1)->addHour(),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'room2-code',
            'status' => ClassSession::STATUS_SCHEDULED,
            'created_by' => $this->instructor2->id,
        ]);

        // Personal event for Student 1
        $personalS1 = Event::create([
            'title' => 'Student 1 Study Group',
            'type' => Event::TYPE_PERSONAL,
            'starts_at' => now()->addDays(4),
            'user_id' => $this->student1->id,
            'created_by' => $this->student1->id,
        ]);

        // Personal event for Student 2
        $personalS2 = Event::create([
            'title' => 'Student 2 Private Diary',
            'type' => Event::TYPE_PERSONAL,
            'starts_at' => now()->addDays(4),
            'user_id' => $this->student2->id,
            'created_by' => $this->student2->id,
        ]);

        // General event
        $generalEvent = Event::create([
            'title' => 'Campus Holiday',
            'type' => Event::TYPE_GENERAL,
            'starts_at' => now()->addDays(5),
            'created_by' => $this->admin->id,
        ]);

        // Retrieve items for Student 1
        $items = $calendarService->getItemsForUser($this->student1, now()->startOfDay(), now()->addDays(10)->endOfDay());
        $titles = $items->pluck('title')->all();

        // Must include:
        $this->assertContains('Course 1 Final Review', $titles);
        $this->assertContains('[Live Class] Live Lab Course 1', $titles);
        $this->assertContains('Student 1 Study Group', $titles);
        $this->assertContains('Campus Holiday', $titles);

        // Must NOT include items from Course 2 or Student 2's personal notes:
        $this->assertNotContains('Course 2 Secret Meeting', $titles);
        $this->assertNotContains('[Live Class] Live Lab Course 2', $titles);
        $this->assertNotContains('Student 2 Private Diary', $titles);
    }

    public function test_student_cannot_join_before_15_minute_window_or_after_session_ends(): void
    {
        // 1. Session in future (> 15 minutes away)
        $futureSession = ClassSession::create([
            'course_id' => $this->course1->id,
            'title' => 'Future Class',
            'starts_at' => now()->addHours(2),
            'ends_at' => now()->addHours(3),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'code-future',
            'status' => ClassSession::STATUS_SCHEDULED,
            'created_by' => $this->instructor1->id,
        ]);

        // Student 1 cannot join 2 hours before -> 403
        $this->actingAs($this->student1)->get("/classes/{$futureSession->id}")
            ->assertStatus(403);

        // 2. Session starting in 10 minutes (within 15-minute window)
        $upcomingSession = ClassSession::create([
            'course_id' => $this->course1->id,
            'title' => 'Upcoming Class',
            'starts_at' => now()->addMinutes(10),
            'ends_at' => now()->addHour(),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'code-upcoming',
            'status' => ClassSession::STATUS_SCHEDULED,
            'created_by' => $this->instructor1->id,
        ]);

        // Student 1 CAN join within 15 mins
        $this->actingAs($this->student1)->get("/classes/{$upcomingSession->id}")
            ->assertStatus(200);

        // 3. Session is LIVE
        $liveSession = ClassSession::create([
            'course_id' => $this->course1->id,
            'title' => 'Active Live Class',
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addMinutes(45),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'code-live',
            'status' => ClassSession::STATUS_LIVE,
            'created_by' => $this->instructor1->id,
        ]);

        $this->actingAs($this->student1)->get("/classes/{$liveSession->id}")
            ->assertStatus(200);

        // 4. Session is ENDED
        $endedSession = ClassSession::create([
            'course_id' => $this->course1->id,
            'title' => 'Past Class',
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subHour(),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'code-ended',
            'status' => ClassSession::STATUS_ENDED,
            'created_by' => $this->instructor1->id,
        ]);

        // Student 1 cannot join ended session -> 403
        $this->actingAs($this->student1)->get("/classes/{$endedSession->id}")
            ->assertStatus(403);
    }

    public function test_instructor_cannot_create_session_for_another_instructors_course(): void
    {
        // Instructor 1 attempts to create a session for Course 2 (owned by Instructor 2)
        $response = $this->actingAs($this->instructor1)
            ->post('/instructor/classes', [
                'course_id' => $this->course2->id,
                'title' => 'Illicit Class Takeover',
                'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
                'provider' => ClassSession::PROVIDER_JITSI,
            ]);

        // Rejected with 403 Forbidden
        $response->assertStatus(403);
        $this->assertDatabaseMissing('class_sessions', [
            'title' => 'Illicit Class Takeover',
        ]);
    }

    public function test_attendance_is_recorded_only_once_per_user(): void
    {
        $liveSession = ClassSession::create([
            'course_id' => $this->course1->id,
            'title' => 'Attendance Testing Session',
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addMinutes(45),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'code-attend',
            'status' => ClassSession::STATUS_LIVE,
            'created_by' => $this->instructor1->id,
        ]);

        // Student 1 joins first time
        $this->actingAs($this->student1)->get("/classes/{$liveSession->id}")
            ->assertStatus(200);

        $this->assertDatabaseCount('class_attendances', 1);

        // Student 1 rejoins / refreshes page
        $this->actingAs($this->student1)->get("/classes/{$liveSession->id}")
            ->assertStatus(200);

        // Still exactly 1 attendance record!
        $this->assertDatabaseCount('class_attendances', 1);
        $this->assertDatabaseHas('class_attendances', [
            'session_id' => $liveSession->id,
            'user_id' => $this->student1->id,
        ]);
    }
}
