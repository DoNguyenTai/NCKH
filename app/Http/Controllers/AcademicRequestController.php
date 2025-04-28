<?php

namespace App\Http\Controllers;

use App\Models\AcademicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AcademicRequestController extends Controller
{
    public function create()
    {
        return view('academic_requests.create');
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'student_id' => 'required|string',
            'request_type' => 'required|string',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.key' => 'required|string',
            'custom_fields.*.value' => 'nullable|string',
        ]);

        AcademicRequest::create([
            'student_id' => $data['student_id'],
            'request_type' => $data['request_type'],
            'custom_fields' => $data['custom_fields'],
        ]);

        return redirect()->route('academic_requests.create')->with('success', 'Đơn học vụ đã được lưu!');
    }

    public function login() {
        return view('login.login');
    }

    public function loginProcess(Request $request) {
        $mssv = $request->mssv;
        $response = Http::get("http://event.fittdc.info/api/students/$mssv");
    
        // Kiểm tra phản hồi có hợp lệ không
        if ($response->successful()) {
            $data = json_decode($response->body(), true);
    
            if (is_array($data)) {
                return view('login.show', ["data" => $data]);
            } else {
                return response()->json(['error' => 'Dữ liệu trả về không hợp lệ'], 500);
            }
        }
    
        return response()->json(['error' => 'Không thể lấy dữ liệu từ API'], 500);
    }
}
