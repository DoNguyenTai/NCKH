<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = Template::create([
            'name' => 'Mẫu đơn xin nghỉ học'
        ]);

        $template->items()->createMany([
            [
                'type' => 'title',
                'top' => 50,
                'value' => 'ĐƠN XIN NGHỈ HỌC'
            ],
            [
                'type' => 'input',
                'top' => 150,
                'left' => 100,
                'value' => 'Nguyễn Văn A'
            ],
            [
                'type' => 'studentForm',
                'top' => 250,
                'data' => [
                    'name' => 'Nguyễn Văn A',
                    'dob' => '2001-01-01',
                    'studentId' => 'SV123456'
                ]
            ]
        ]);
    }
}
