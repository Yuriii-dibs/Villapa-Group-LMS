<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Message;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::where('email', 'instructor@school.test')->first();
        $instructor2 = User::where('email', 'instructor2@school.test')->first();
        $student = User::where('email', 'student@school.test')->first();
        $student2 = User::where('email', 'student2@school.test')->first();
        $admin = User::where('email', 'admin@school.test')->first();

        if (!$instructor || !$student) {
            return;
        }

        // 1. Categories — point sample courses at the UEP IT college category
        $catIT = Category::firstOrCreate(['slug' => 'college-of-information-technology'], [
            'name'        => 'College of Information Technology',
            'description' => 'Bachelor of Science in Information Technology program.',
        ]);

        $catWeb    = $catIT;
        $catDesign = $catIT;
        $catData   = $catIT;

        // 2. Published Course 1
        $course1 = Course::firstOrCreate(['slug' => 'modern-full-stack-web-development'], [
            'instructor_id' => $instructor->id,
            'category_id' => $catWeb->id,
            'title' => 'Modern Full-Stack Web Development',
            'description' => 'Master modern web development from database design with Eloquent to dynamic user interfaces with Tailwind CSS and Alpine.js.',
            'learning_objectives' => [
                'Build reactive interfaces with Alpine.js and Tailwind CSS',
                'Design clean relational databases with Laravel Eloquent',
                'Implement secure role-based access control and RESTful APIs',
            ],
            'difficulty' => Course::DIFFICULTY_INTERMEDIATE,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
            'estimated_duration_minutes' => 360,
            'published_at' => now()->subDays(5),
        ]);

        // Modules & Lessons for Course 1
        $mod1 = Module::firstOrCreate([
            'course_id' => $course1->id,
            'title' => 'Module 1: Foundations & Architecture',
        ], [
            'description' => 'Core concepts of modern web applications.',
            'order' => 1,
            'is_published' => true,
        ]);

        $lesson1 = Lesson::firstOrCreate([
            'module_id' => $mod1->id,
            'title' => 'Introduction to Modern Web Architecture',
        ], [
            'slug' => 'intro-modern-web-arch',
            'content_type' => Lesson::TYPE_TEXT,
            'body' => "<p>Welcome to <strong>Modern Full-Stack Web Development</strong>! In this lesson, we will explore the core layers of modern web architecture: the Presentation layer, Application logic, and the Database layer.</p><p>Key principles include separation of concerns, DRY (Don't Repeat Yourself), and security best practices such as CSRF protection and prepared SQL statements.</p>",
            'order' => 1,
            'duration_minutes' => 25,
            'is_preview' => true,
            'is_published' => true,
        ]);

        $lesson2 = Lesson::firstOrCreate([
            'module_id' => $mod1->id,
            'title' => 'Controllers, Routing, and Middleware',
        ], [
            'slug' => 'controllers-routing-middleware',
            'content_type' => Lesson::TYPE_TEXT,
            'body' => "<p>Discover how HTTP requests travel through route definitions and middleware filters before reaching dedicated controller actions.</p><p>You will learn how to protect routes with RoleMiddleware and sanitize incoming form requests.</p>",
            'order' => 2,
            'duration_minutes' => 35,
            'is_preview' => false,
            'is_published' => true,
        ]);

        $mod2 = Module::firstOrCreate([
            'course_id' => $course1->id,
            'title' => 'Module 2: Database Design & Eloquent',
        ], [
            'description' => 'Hands-on database relationships and queries.',
            'order' => 2,
            'is_published' => true,
        ]);

        $lesson3 = Lesson::firstOrCreate([
            'module_id' => $mod2->id,
            'title' => 'Relational Schemas and Eloquent ORM',
        ], [
            'slug' => 'relational-schemas-and-eloquent',
            'content_type' => Lesson::TYPE_TEXT,
            'body' => "<p>In this lesson, we model one-to-many and many-to-many relationships using Eloquent.</p><p>We also discuss query optimization using eager loading to prevent N+1 query bottlenecks.</p>",
            'order' => 1,
            'duration_minutes' => 45,
            'is_preview' => false,
            'is_published' => true,
        ]);

        // 3. Published Course 2 (Design)
        $course2 = Course::firstOrCreate(['slug' => 'design-systems-with-tailwind'], [
            'instructor_id' => $instructor2 ? $instructor2->id : $instructor->id,
            'category_id' => $catDesign->id,
            'title' => 'Design Systems & UI Engineering',
            'description' => 'Learn how to construct cohesive visual hierarchies, typography scales, responsive layouts, and reusable components using Tailwind CSS.',
            'learning_objectives' => [
                'Construct accessibility-first component libraries',
                'Master responsive grid and flexbox utility patterns',
                'Design accessible color palettes and focus states',
            ],
            'difficulty' => Course::DIFFICULTY_BEGINNER,
            'approval_status' => Course::APPROVAL_APPROVED,
            'publication_status' => Course::PUB_PUBLISHED,
            'estimated_duration_minutes' => 180,
            'published_at' => now()->subDays(3),
        ]);

        $modDesign = Module::firstOrCreate([
            'course_id' => $course2->id,
            'title' => 'Foundations of Design Systems',
        ], [
            'description' => 'Color, spacing, and typography scales.',
            'order' => 1,
            'is_published' => true,
        ]);

        Lesson::firstOrCreate([
            'module_id' => $modDesign->id,
            'title' => 'Color Tokens and Contrast Guidelines',
        ], [
            'slug' => 'color-tokens-contrast',
            'content_type' => Lesson::TYPE_TEXT,
            'body' => "<p>An introduction to WCAG 2.1 AA accessibility standards, contrast ratios, and building semantic color tokens.</p>",
            'order' => 1,
            'duration_minutes' => 20,
            'is_preview' => true,
            'is_published' => true,
        ]);

        // 4. Pending Course (for Admin approval workflow demo)
        Course::firstOrCreate(['slug' => 'intro-data-science-python'], [
            'instructor_id' => $instructor->id,
            'category_id' => $catData->id,
            'title' => 'Introduction to Data Science with Python',
            'description' => 'An introductory journey into data analytics, Pandas dataframes, and statistical visualization.',
            'learning_objectives' => [
                'Manipulate tabular datasets with Pandas',
                'Generate insightful visualizations with Matplotlib',
            ],
            'difficulty' => Course::DIFFICULTY_BEGINNER,
            'approval_status' => Course::APPROVAL_PENDING,
            'publication_status' => Course::PUB_DRAFT,
            'estimated_duration_minutes' => 240,
        ]);

        // 5. Enroll Student 1 in Course 1 with partial progress
        CourseEnrollment::firstOrCreate([
            'course_id' => $course1->id,
            'user_id' => $student->id,
        ], [
            'status' => CourseEnrollment::STATUS_ACTIVE,
            'enrolled_at' => now()->subDays(2),
        ]);

        LessonProgress::firstOrCreate([
            'user_id' => $student->id,
            'lesson_id' => $lesson1->id,
        ], [
            'is_completed' => true,
            'completed_at' => now()->subDays(1),
        ]);

        // Enroll Student 2 in Course 1 as well
        if ($student2) {
            CourseEnrollment::firstOrCreate([
                'course_id' => $course1->id,
                'user_id' => $student2->id,
            ], [
                'status' => CourseEnrollment::STATUS_ACTIVE,
                'enrolled_at' => now()->subDay(),
            ]);
        }

        // 6. Online Class Session
        ClassSession::firstOrCreate([
            'course_id' => $course1->id,
            'title' => 'Live Q&A: Architectural Review',
        ], [
            'description' => 'Weekly interactive office hours covering Eloquent relationships and REST endpoints.',
            'starts_at' => now()->addHours(2),
            'ends_at' => now()->addHours(3),
            'provider' => 'jitsi',
            'room_code' => Str::random(12),
            'status' => 'scheduled',
            'created_by' => $instructor->id,
        ]);

        // 7. Calendar Events
        Event::firstOrCreate([
            'title' => 'Course 1 Live Q&A Office Hours',
            'course_id' => $course1->id,
        ], [
            'description' => 'Join the instructor live on Jitsi Meet for troubleshooting.',
            'type' => 'class',
            'starts_at' => now()->addHours(2),
            'ends_at' => now()->addHours(3),
            'created_by' => $instructor->id,
        ]);

        if ($admin) {
            Event::firstOrCreate([
                'title' => 'Midterm Academic Review Period',
                'course_id' => null,
                'user_id' => null,
            ], [
                'description' => 'Campus-wide consultation and review sessions.',
                'type' => 'general',
                'starts_at' => now()->addDays(3)->setHour(9)->setMinute(0),
                'ends_at' => now()->addDays(5)->setHour(17)->setMinute(0),
                'created_by' => $admin->id,
            ]);
        }

        // 8. Sample Chat Message
        Message::firstOrCreate([
            'course_id' => $course1->id,
            'sender_id' => $instructor->id,
            'recipient_id' => null,
            'body' => 'Welcome to the course! Feel free to post questions here or message me directly.',
        ], [
            'created_at' => now()->subDays(2),
        ]);

        Message::firstOrCreate([
            'course_id' => $course1->id,
            'sender_id' => $student->id,
            'recipient_id' => null,
            'body' => 'Thank you Prof. Alex! Excited to start the Eloquent module.',
        ], [
            'created_at' => now()->subDay(),
        ]);
    }
}
