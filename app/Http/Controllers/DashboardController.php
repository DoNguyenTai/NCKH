<?php

namespace App\Http\Controllers;

use App\Models\RequestStudent;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = RequestStudent::with(['folder:id,name', 'student:student_code,name'])->count();

        return response()->json($data);
    }
    public function statusWaiting()
    {
        $data = RequestStudent::with(['folder:id,name', 'student:student_code,name'])->where('status', '=', 'Đang chờ duyệt')->count();

        return response()->json($data);
    }
    public function statusSuccess()
    {
        $data = RequestStudent::with(['folder:id,name', 'student:student_code,name'])->where('status', '=', 'Đã duyệt')->count();

        return response()->json($data);
    }
    public function totalNumberOfUser(){
        $data=Student::all()->count();
        return response()->json($data);
    }
}
