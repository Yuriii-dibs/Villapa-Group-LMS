<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Event;
use App\Models\Message;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatCalendarAndClassPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $instructor;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $studentRole = Role::firstOrCreate(['name' => Role::STUDENT], ['display_name' => 'Student']);
        $instructorRole = Role::firstOrCreate(['name' => Role::INSTRUCTOR], ['display_name' => 'Instructor']);

        $category = Category::create([
            'name' => 'Web Development',
            'slug' => 'web-development',
        ]);

        $this->instructor = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $this->instructor->roles()->attach($instructorRole);

        $this->student = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $this->student->roles()->attach($studentRole);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'title' => 'Fullstack Laravel Mastery',
            'slug' => 'fullstack-laravel-mastery',
            'description' => 'Complete modern Laravel course.',
            'difficulty' => Course::DIFFICULTY_INTERMEDIATE,
            'publication_status' => Course::PUB_PUBLISHED,
            'approval_status' => Course::APPROVAL_APPROVED,
            'published_at' => now(),
        ]);

        CourseEnrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'enrolled_at' => now(),
        ]);
    }

    public function test_course_page_displays_chat_and_online_classes_tabs(): void
    {
        $session = ClassSession::create([
            'course_id' => $this->course->id,
            'title' => 'Live Orientation Q&A',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'room1234567890ab',
            'status' => ClassSession::STATUS_SCHEDULED,
            'created_by' => $this->instructor->id,
        ]);

        $response = $this->actingAs($this->student)->get(route('student.courses.show', $this->course));

        $response->assertOk();
        $response->assertSee('Syllabus');
        $response->assertSee('Course Chat');
        $response->assertSee('Online Classes');
        $response->assertSee('Course Discussion Room');
        $response->assertSee('Live Orientation Q&A');
    }

    public function test_messages_page_lists_course_rooms_and_direct_contacts(): void
    {
        $response = $this->actingAs($this->student)->get(route('chat.index'));

        $response->assertOk();
        $response->assertSee('Communication & Discussions', false);
        $response->assertSee($this->course->title);
        $response->assertSee($this->instructor->name);
        $response->assertSee('Open Room');
        $response->assertSee('Message');
    }

    public function test_calendar_page_displays_month_grid_and_upcoming_items(): void
    {
        Event::create([
            'title' => 'Milestone Review Task',
            'type' => Event::TYPE_PERSONAL,
            'starts_at' => now()->addDay(),
            'user_id' => $this->student->id,
            'created_by' => $this->student->id,
        ]);

        $response = $this->actingAs($this->student)->get(route('calendar.index'));

        $response->assertOk();
        $response->assertSee('Academic Calendar');
        $response->assertSee('Upcoming Next 7 Days');
        $response->assertSee('Milestone Review Task');
        $response->assertSee('+ Add Event');
    }

    public function test_join_class_page_renders_leave_button_and_attendance_for_instructor(): void
    {
        $session = ClassSession::create([
            'course_id' => $this->course->id,
            'title' => 'Live Lab Workshop',
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addHour(),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'lab1234567890abcd',
            'status' => ClassSession::STATUS_LIVE,
            'created_by' => $this->instructor->id,
        ]);

        // Student opens live class
        $studentResp = $this->actingAs($this->student)->get(route('classes.show', $session));
        $studentResp->assertOk();
        $studentResp->assertSee('Live Class: Live Lab Workshop');
        $studentResp->assertSee('Leave Class');
        $studentResp->assertSee('Attendance Logged');

        // Instructor opens live class and sees attendance roster
        $instructorResp = $this->actingAs($this->instructor)->get(route('classes.show', $session));
        $instructorResp->assertOk();
        $instructorResp->assertSee('Leave Class');
        $instructorResp->assertSee('Session Attendance Roster');
        $instructorResp->assertSee($this->student->name);
    }

    public function test_dashboards_show_next_class_and_next_5_events(): void
    {
        ClassSession::create([
            'course_id' => $this->course->id,
            'title' => 'Upcoming Live Masterclass',
            'starts_at' => now()->addHours(2),
            'ends_at' => now()->addHours(3),
            'provider' => ClassSession::PROVIDER_JITSI,
            'room_code' => 'masterclass123456',
            'status' => ClassSession::STATUS_SCHEDULED,
            'created_by' => $this->instructor->id,
        ]);

        Event::create([
            'title' => 'Midterm Project Deadline',
            'type' => Event::TYPE_EXAM,
            'starts_at' => now()->addDays(3),
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
        ]);

        // Student Dashboard verification
        $studentDash = $this->actingAs($this->student)->get(route('student.dashboard'));
        $studentDash->assertOk();
        $studentDash->assertSee('Next Online Class');
        $studentDash->assertSee('Upcoming Live Masterclass');
        $studentDash->assertSee('Next 5 Calendar Events');
        $studentDash->assertSee('Midterm Project Deadline');

        // Instructor Dashboard verification
        $instructorDash = $this->actingAs($this->instructor)->get(route('instructor.dashboard'));
        $instructorDash->assertOk();
        $instructorDash->assertSee('Next Teaching Session');
        $instructorDash->assertSee('Upcoming Live Masterclass');
        $instructorDash->assertSee('Next 5 Calendar Events');
        $instructorDash->assertSee('Midterm Project Deadline');
    }
}
