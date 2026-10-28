<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class UEPCoursesSeeder extends Seeder
{
    public function run(): void
    {
        // Use the first available instructor, or fall back to admin
        $instructor = User::where('email', 'instructor@school.test')->first()
            ?? User::whereHas('roles', fn($q) => $q->where('name', 'instructor'))->first()
            ?? User::first();

        if (!$instructor) {
            $this->command->warn('No instructor found. Skipping UEP courses seeder.');
            return;
        }

        // ─────────────────────────────────────────────
        // CATEGORIES (one per college)
        // ─────────────────────────────────────────────
        $catEd = Category::firstOrCreate(['slug' => 'college-of-education'], [
            'name'        => 'College of Education',
            'description' => 'Programs in elementary and secondary education with diverse major specializations.',
        ]);

        $catBusiness = Category::firstOrCreate(['slug' => 'college-of-business-and-accountancy'], [
            'name'        => 'College of Business & Accountancy',
            'description' => 'Accounting, business administration, and accounting information systems programs.',
        ]);

        $catIT = Category::firstOrCreate(['slug' => 'college-of-information-technology'], [
            'name'        => 'College of Information Technology',
            'description' => 'Bachelor of Science in Information Technology program.',
        ]);

        $catCrim = Category::firstOrCreate(['slug' => 'college-of-criminology'], [
            'name'        => 'College of Criminology',
            'description' => 'Bachelor of Science in Criminology program.',
        ]);

        $catHealth = Category::firstOrCreate(['slug' => 'college-of-health-sciences'], [
            'name'        => 'College of Health Sciences',
            'description' => 'Midwifery degree and diploma programs.',
        ]);

        // ─────────────────────────────────────────────
        // COLLEGE OF EDUCATION
        // ─────────────────────────────────────────────
        $edCourses = [
            [
                'slug'        => 'bachelor-of-elementary-education',
                'title'       => 'Bachelor of Elementary Education (BEEd)',
                'description' => 'A four-year degree program that prepares future teachers for Grades 1–6 instruction, covering child development, pedagogy, and curriculum design.',
                'objectives'  => [
                    'Apply developmentally appropriate teaching strategies for elementary learners',
                    'Design effective lesson plans aligned with the K-12 curriculum',
                    'Demonstrate professional and ethical responsibilities of an educator',
                ],
            ],
            [
                'slug'        => 'bsed-english',
                'title'       => 'Bachelor of Secondary Education – Major in English',
                'description' => 'Prepares students to teach English Language and Literature in secondary schools with a strong foundation in linguistics and communication.',
                'objectives'  => [
                    'Teach English grammar, literature, and composition at the secondary level',
                    'Apply communicative language teaching methodologies',
                    'Design culturally responsive English language curricula',
                ],
            ],
            [
                'slug'        => 'bsed-mathematics',
                'title'       => 'Bachelor of Secondary Education – Major in Mathematics',
                'description' => 'Develops skilled mathematics educators equipped with strong content knowledge and pedagogical skills for Grades 7–12.',
                'objectives'  => [
                    'Demonstrate mastery of secondary mathematics content areas',
                    'Apply problem-solving and inquiry-based instruction techniques',
                    'Use technology effectively in mathematics teaching',
                ],
            ],
            [
                'slug'        => 'bsed-science',
                'title'       => 'Bachelor of Secondary Education – Major in Science',
                'description' => 'Produces competent science teachers proficient in Biology, Chemistry, Physics, and Earth Science for secondary education.',
                'objectives'  => [
                    'Conduct laboratory investigations and science demonstrations safely',
                    'Integrate STEM approaches into secondary science instruction',
                    'Evaluate and develop science curriculum materials',
                ],
            ],
            [
                'slug'        => 'bsed-filipino',
                'title'       => 'Bachelor of Secondary Education – Major in Filipino',
                'description' => 'Trains educators to teach Filipino language and literature, promoting national language proficiency and cultural appreciation.',
                'objectives'  => [
                    'Teach Filipino grammar, reading, and literature at the secondary level',
                    'Promote national identity through language and literary study',
                    'Apply communicative approaches in Filipino language instruction',
                ],
            ],
            [
                'slug'        => 'bsed-social-studies',
                'title'       => 'Bachelor of Secondary Education – Major in Social Studies',
                'description' => 'Equips teachers with knowledge in History, Geography, Economics, and Civics to develop informed and responsible citizens.',
                'objectives'  => [
                    'Integrate interdisciplinary social science content into instruction',
                    'Use inquiry-based approaches to teach historical thinking',
                    'Foster civic competence and critical understanding of society',
                ],
            ],
            [
                'slug'        => 'bsed-values-education',
                'title'       => 'Bachelor of Secondary Education – Major in Values Education',
                'description' => 'Prepares educators to guide students in moral, ethical, and values formation essential to holistic development.',
                'objectives'  => [
                    'Facilitate values formation and character development among adolescents',
                    'Apply principles of moral philosophy and ethics in teaching',
                    'Create a positive and values-driven classroom environment',
                ],
            ],
        ];

        foreach ($edCourses as $c) {
            $course = Course::firstOrCreate(['slug' => $c['slug']], [
                'instructor_id'              => $instructor->id,
                'category_id'               => $catEd->id,
                'title'                      => $c['title'],
                'description'               => $c['description'],
                'learning_objectives'        => $c['objectives'],
                'difficulty'                 => Course::DIFFICULTY_BEGINNER,
                'approval_status'            => Course::APPROVAL_APPROVED,
                'publication_status'         => Course::PUB_PUBLISHED,
                'estimated_duration_minutes' => 480,
                'published_at'               => now()->subDays(10),
            ]);
            $this->seedIntroModule($course, $c['title']);
        }

        // ─────────────────────────────────────────────
        // COLLEGE OF BUSINESS & ACCOUNTANCY
        // ─────────────────────────────────────────────
        $bizCourses = [
            [
                'slug'        => 'bs-accountancy',
                'title'       => 'Bachelor of Science in Accountancy',
                'description' => 'A rigorous program preparing students for the CPA Licensure Examination, covering financial accounting, auditing, taxation, and management accounting.',
                'objectives'  => [
                    'Apply Generally Accepted Accounting Principles (GAAP) and IFRS standards',
                    'Prepare and analyze financial statements for various entities',
                    'Pass the CPA Licensure Examination and uphold professional ethics',
                ],
            ],
            [
                'slug'        => 'bs-accounting-information-system',
                'title'       => 'BS Accounting Information System',
                'description' => 'Combines accounting principles with information technology to equip graduates in designing and managing modern accounting systems.',
                'objectives'  => [
                    'Design and evaluate accounting information systems for businesses',
                    'Apply database management and ERP systems in an accounting context',
                    'Ensure data integrity, security, and compliance in financial systems',
                ],
            ],
            [
                'slug'        => 'bsba-financial-management',
                'title'       => 'BS Business Administration – Major in Financial Management',
                'description' => 'Trains students in corporate finance, investments, and financial planning to drive sound financial decision-making in organizations.',
                'objectives'  => [
                    'Analyze financial data and prepare strategic financial plans',
                    'Evaluate investment opportunities and manage financial risk',
                    'Apply capital budgeting and working capital management techniques',
                ],
            ],
            [
                'slug'        => 'bsba-marketing-management',
                'title'       => 'BS Business Administration – Major in Marketing Management',
                'description' => 'Develops marketing professionals skilled in consumer behavior, branding, digital marketing, and strategic market planning.',
                'objectives'  => [
                    'Develop and execute integrated marketing communication campaigns',
                    'Analyze consumer behavior and market trends to inform strategy',
                    'Manage product, pricing, distribution, and promotion decisions',
                ],
            ],
            [
                'slug'        => 'bsba-operations-management',
                'title'       => 'BS Business Administration – Major in Operations Management',
                'description' => 'Focuses on optimizing business processes, supply chain management, quality control, and production systems.',
                'objectives'  => [
                    'Design and improve operational processes for efficiency and quality',
                    'Apply supply chain and logistics management principles',
                    'Implement quality management systems and lean methodologies',
                ],
            ],
            [
                'slug'        => 'bsba-human-resource-management',
                'title'       => 'BS Business Administration – Major in Human Resource Management',
                'description' => 'Prepares HR professionals in talent acquisition, employee development, labor relations, and organizational behavior.',
                'objectives'  => [
                    'Implement effective recruitment, selection, and onboarding processes',
                    'Design and manage employee training and development programs',
                    'Apply labor law, compensation management, and HR analytics',
                ],
            ],
        ];

        foreach ($bizCourses as $c) {
            $course = Course::firstOrCreate(['slug' => $c['slug']], [
                'instructor_id'              => $instructor->id,
                'category_id'               => $catBusiness->id,
                'title'                      => $c['title'],
                'description'               => $c['description'],
                'learning_objectives'        => $c['objectives'],
                'difficulty'                 => Course::DIFFICULTY_BEGINNER,
                'approval_status'            => Course::APPROVAL_APPROVED,
                'publication_status'         => Course::PUB_PUBLISHED,
                'estimated_duration_minutes' => 480,
                'published_at'               => now()->subDays(8),
            ]);
            $this->seedIntroModule($course, $c['title']);
        }

        // ─────────────────────────────────────────────
        // COLLEGE OF INFORMATION TECHNOLOGY
        // ─────────────────────────────────────────────
        $itCourse = Course::firstOrCreate(['slug' => 'bs-information-technology'], [
            'instructor_id'              => $instructor->id,
            'category_id'               => $catIT->id,
            'title'                      => 'Bachelor of Science in Information Technology',
            'description'               => 'A comprehensive four-year program covering programming, networking, databases, cybersecurity, and systems analysis to prepare IT professionals for the digital economy.',
            'learning_objectives'        => [
                'Develop software applications using modern programming languages and frameworks',
                'Design, implement, and manage relational and non-relational databases',
                'Apply networking principles and cybersecurity best practices',
                'Analyze and design information systems for organizational needs',
            ],
            'difficulty'                 => Course::DIFFICULTY_BEGINNER,
            'approval_status'            => Course::APPROVAL_APPROVED,
            'publication_status'         => Course::PUB_PUBLISHED,
            'estimated_duration_minutes' => 480,
            'published_at'               => now()->subDays(6),
        ]);
        $this->seedIntroModule($itCourse, 'Bachelor of Science in Information Technology');

        // ─────────────────────────────────────────────
        // COLLEGE OF CRIMINOLOGY
        // ─────────────────────────────────────────────
        $crimCourse = Course::firstOrCreate(['slug' => 'bs-criminology'], [
            'instructor_id'              => $instructor->id,
            'category_id'               => $catCrim->id,
            'title'                      => 'Bachelor of Science in Criminology',
            'description'               => 'Prepares students for careers in law enforcement, corrections, and criminal justice through the study of crime causation, criminal law, forensic science, and police administration.',
            'learning_objectives'        => [
                'Explain theories of crime causation and criminal behavior',
                'Apply criminal law and jurisprudence in case analysis',
                'Demonstrate skills in forensic investigation and crime scene processing',
                'Understand police, corrections, and court systems in the Philippines',
            ],
            'difficulty'                 => Course::DIFFICULTY_BEGINNER,
            'approval_status'            => Course::APPROVAL_APPROVED,
            'publication_status'         => Course::PUB_PUBLISHED,
            'estimated_duration_minutes' => 480,
            'published_at'               => now()->subDays(7),
        ]);
        $this->seedIntroModule($crimCourse, 'Bachelor of Science in Criminology');

        // ─────────────────────────────────────────────
        // COLLEGE OF HEALTH SCIENCES
        // ─────────────────────────────────────────────
        $healthCourses = [
            [
                'slug'        => 'bs-midwifery',
                'title'       => 'Bachelor of Science in Midwifery',
                'description' => 'A four-year degree program that develops professional midwives skilled in maternal and newborn care, reproductive health, and community health nursing.',
                'objectives'  => [
                    'Provide safe, competent, and compassionate midwifery care during pregnancy, labor, and postpartum',
                    'Apply evidence-based practices in maternal, newborn, and reproductive health',
                    'Perform emergency obstetric and newborn care procedures competently',
                ],
            ],
            [
                'slug'        => 'diploma-in-midwifery',
                'title'       => 'Diploma in Midwifery (2-Year)',
                'description' => 'A two-year diploma program providing foundational clinical training in midwifery practice, focusing on essential maternal and newborn health competencies.',
                'objectives'  => [
                    'Demonstrate core midwifery competencies in prenatal, intrapartum, and postnatal care',
                    'Identify and manage common maternal and neonatal complications',
                    'Apply professional and ethical standards in midwifery practice',
                ],
            ],
        ];

        foreach ($healthCourses as $c) {
            $course = Course::firstOrCreate(['slug' => $c['slug']], [
                'instructor_id'              => $instructor->id,
                'category_id'               => $catHealth->id,
                'title'                      => $c['title'],
                'description'               => $c['description'],
                'learning_objectives'        => $c['objectives'],
                'difficulty'                 => Course::DIFFICULTY_BEGINNER,
                'approval_status'            => Course::APPROVAL_APPROVED,
                'publication_status'         => Course::PUB_PUBLISHED,
                'estimated_duration_minutes' => 360,
                'published_at'               => now()->subDays(5),
            ]);
            $this->seedIntroModule($course, $c['title']);
        }

        $this->command->info('✅ UEP courses seeded: 5 colleges, 16 programs.');
    }

    /**
     * Seed a simple introductory module with one preview lesson for a course.
     */
    private function seedIntroModule(Course $course, string $courseTitle): void
    {
        $module = Module::firstOrCreate([
            'course_id' => $course->id,
            'title'     => 'Module 1: Program Overview & Orientation',
        ], [
            'description'  => 'Introduction to the program, its objectives, and the academic requirements.',
            'order'        => 1,
            'is_published' => true,
        ]);

        Lesson::firstOrCreate([
            'module_id' => $module->id,
            'title'     => 'Welcome to ' . $courseTitle,
        ], [
            'slug'             => 'welcome-' . $course->slug,
            'content_type'     => Lesson::TYPE_TEXT,
            'body'             => '<p>Welcome to <strong>' . e($courseTitle) . '</strong> at the University of Eastern Pangasinan (UEP).</p>'
                . '<p>This introductory lesson outlines the program structure, academic requirements, learning outcomes, and the support resources available to you throughout your studies.</p>'
                . '<p>We look forward to accompanying you on your academic journey. Let\'s begin!</p>',
            'order'            => 1,
            'duration_minutes' => 20,
            'is_preview'       => true,
            'is_published'     => true,
        ]);
    }
}
