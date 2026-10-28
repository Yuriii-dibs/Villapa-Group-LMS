<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        Subject::truncate();

        $bsitSubjects = [
            [
                'subject_code' => 'NET 102',
                'title'        => 'Networking 2',
                'program'      => 'BSIT',
                'year_level'   => 3,
                'section'      => 'IT3-J',
                'block'        => 'J',
                'instructor'   => 'SARMIENTO HAROLD ALMOITE',
                'fused_with'   => null,
                'schedules'    => [
                    ['day' => 'T',  'time_start' => '12:00 PM', 'time_end' => '02:00 PM', 'type' => 'Lec', 'room' => 'ONLINE - ONLINE'],
                    ['day' => 'T',  'time_start' => '07:00 AM', 'time_end' => '10:00 AM', 'type' => 'Lab', 'room' => 'ONLINE - ONLINE'],
                ],
            ],
            [
                'subject_code' => 'IT ELEC 2',
                'title'        => 'IT Elective 2 (Social and Professional Issues)',
                'program'      => 'BSIT',
                'year_level'   => 3,
                'section'      => 'IT3-J',
                'block'        => 'J',
                'instructor'   => 'TORRES JENKIELYN CORTEZ',
                'fused_with'   => null,
                'schedules'    => [
                    ['day' => 'M',  'time_start' => '10:00 AM', 'time_end' => '12:00 PM', 'type' => 'Lec', 'room' => 'ONLINE - ONLINE'],
                    ['day' => 'S',  'time_start' => '01:00 PM', 'time_end' => '04:00 PM', 'type' => 'Lab', 'room' => 'ONLINE - ONLINE'],
                ],
            ],
            [
                'subject_code' => 'UED 101',
                'title'        => 'User Experience Design',
                'program'      => 'BSIT',
                'year_level'   => 3,
                'section'      => 'IT3-J',
                'block'        => 'J',
                'instructor'   => 'PARAGAS BRIAN JERIBC LOPEZ',
                'fused_with'   => null,
                'schedules'    => [
                    ['day' => 'F',  'time_start' => '02:00 PM', 'time_end' => '04:00 PM', 'type' => 'Lec', 'room' => 'DIGITEL - IT ROOM 1'],
                    ['day' => 'W',  'time_start' => '11:00 AM', 'time_end' => '02:00 PM', 'type' => 'Lab', 'room' => 'ONLINE - ONLINE'],
                ],
            ],
            [
                'subject_code' => 'CC 106',
                'title'        => 'App Dev. And Emerging Technologies',
                'program'      => 'BSIT',
                'year_level'   => 3,
                'section'      => 'IT3-J',
                'block'        => 'J',
                'instructor'   => 'Camorangan Samuel Flores',
                'fused_with'   => null,
                'schedules'    => [
                    ['day' => 'T',  'time_start' => '08:00 AM', 'time_end' => '10:00 AM', 'type' => 'Lec', 'room' => 'DIGITEL - IT ROOM 2'],
                    ['day' => 'S',  'time_start' => '08:00 AM', 'time_end' => '11:00 AM', 'type' => 'Lab', 'room' => 'UEP Main Building - CHS LAB'],
                ],
            ],
            [
                'subject_code' => 'GE ELEC 4',
                'title'        => 'Technical Writing',
                'program'      => 'BSIT',
                'year_level'   => 3,
                'section'      => 'IT3-J',
                'block'        => 'J',
                'instructor'   => 'LOCQUIAO NOEL CURIMAO',
                'fused_with'   => null,
                'schedules'    => [
                    ['day' => 'T, TH', 'time_start' => '10:30 AM', 'time_end' => '12:00 PM', 'type' => 'Lec', 'room' => 'Annex D - Annex D RM 6'],
                ],
            ],
            [
                'subject_code' => 'SIA 101',
                'title'        => 'Systems Integration and Architecture 1',
                'program'      => 'BSIT',
                'year_level'   => 3,
                'section'      => 'IT3-J',
                'block'        => 'J',
                'instructor'   => 'Camorangan Samuel Flores',
                'fused_with'   => null,
                'schedules'    => [
                    ['day' => 'W',  'time_start' => '09:00 AM', 'time_end' => '11:00 AM', 'type' => 'Lec', 'room' => 'ONLINE - ONLINE'],
                    ['day' => 'T',  'time_start' => '02:00 PM', 'time_end' => '05:00 PM', 'type' => 'Lab', 'room' => 'ONLINE - ONLINE'],
                ],
            ],
            [
                'subject_code' => 'PF 102',
                'title'        => 'Event Driven Programming',
                'program'      => 'BSIT',
                'year_level'   => 3,
                'section'      => 'IT3-J',
                'block'        => 'J',
                'instructor'   => 'LARRAQUEL MARY ROSE',
                'fused_with'   => null,
                'schedules'    => [
                    ['day' => 'T',  'time_start' => '10:00 AM', 'time_end' => '12:00 PM', 'type' => 'Lec', 'room' => 'Annex D - Annex D RM 6'],
                    ['day' => 'M',  'time_start' => '02:00 PM', 'time_end' => '05:00 PM', 'type' => 'Lab', 'room' => 'ONLINE - ONLINE'],
                ],
            ],
        ];

        foreach ($bsitSubjects as $data) {
            Subject::create($data);
        }
    }
}
