<?php

namespace App\Http\Controllers;


use App\Models\RequestStudent;
use Illuminate\Http\Request;

class RequestStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(RequestStudent::with(['studentId', 'requestType'])->get());
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
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'request_type_id' => 'required|exists:request_types,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        $data['status'] = 'pending';
        $requestStudent = RequestStudent::create($data);

        return response()->json($requestStudent, 201);
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = RequestStudent::with(['studentId', 'requestType'])->findOrFail($id);
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestStudent $RequestStudent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $requestStudent = RequestStudent::findOrFail($id);

        $data = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'request_type_id' => 'sometimes|exists:request_types,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'in:pending,approved,rejected',
            'note' => 'nullable|string',
        ]);

        $requestStudent->update($data);

        return response()->json($requestStudent);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $requestStudent = RequestStudent::findOrFail($id);
        $requestStudent->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    public function showByStudentId($student_id)
    {
        $data = RequestStudent::where('student_id', $student_id)->with('requestType')->get();
        return response()->json($data);
    }

    public function showByRequestTypeId($request_type_id)
    {
        $data = RequestStudent::where('request_type_id', $request_type_id)->with('student')->get();
        return response()->json($data);
    }

    public function searchByID(Request $request)
    {
        $studentId = $request->query('student_id');

        if (!$studentId) {
            return response()->json(['message' => 'student_id is required'], 400);
        }

        $results = RequestStudent::where('student_id', $studentId)->with(['student', 'requestType'])->get();
        return response()->json($results);
    }

    public function searchByStudentName(Request $request)
    {
        $name = $request->query('student_name');

        if (!$name) {
            return response()->json(['message' => 'student_name is required'], 400);
        }

        $results = RequestStudent::whereHas('student', function ($query) use ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        })->with(['student', 'requestType'])->get();

        return response()->json($results);
    }
}
