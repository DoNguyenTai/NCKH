<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Student::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:students,user_id',
            'student_code' => 'required|string|unique:students,student_code',
            'name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'class' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $student = Student::create($validated);

        return response()->json($student, 201);
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $student = Student::findOrFail($id);
        return response()->json($student);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'student_code' => "sometimes|required|string|unique:students,student_code,{$id}",
            'name' => 'sometimes|required|string|max:255',
            'dob' => 'nullable|date',
            'class' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $student->update($validated);

        return response()->json($student);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json(['message' => 'Đã xóa thành công']);
    }

    public function search(Request $request)
    {
        $query = Student::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where('name', 'like', "%$keyword%")
                ->orWhere('student_code', 'like', "%$keyword%")
                ->orWhere('class', 'like', "%$keyword%");
        }

        return response()->json($query->get());
    }
}
