<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NotifyUserMail;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'message' => 'required|string',
            'title' => 'required|string',
        ]);
        $data = [
            'name' => $validated['name'],
            'message' => $validated['message'],
        ];

        $subject = $validated['title'];

        Mail::to($validated['email'])->send(new NotifyUserMail($data, $subject));

        return response()->json(['message' => 'Email đã được gửi thành công!']);
    }

    public function sendMultipleByStudentCode(Request $request)
    {
        $validated = $request->validate([
            'student_codes' => 'required|array',
            'student_codes.*' => 'required|string',
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $subject = $validated['title'];
        $message = $validated['message'];

        // Lấy danh sách sinh viên từ mã số
        $students = Student::whereIn('student_code', $validated['student_codes'])->get();

        $emailsSent = 0;

        foreach ($students as $student) {
            if (!$student->email) {return }; // Bỏ qua nếu không có email

            $data = [
                'name' => $student->name,
                'message' => $message,
            ];

            try {
                Mail::to($student->email)->send(new NotifyUserMail($data, $subject));
                $emailsSent++;
            } catch (\Exception $e) {
                \Log::error("Lỗi gửi email đến {$student->email}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Đã gửi email đến {$emailsSent} sinh viên.",
        ]);
    }
}
