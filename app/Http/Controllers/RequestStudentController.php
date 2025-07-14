<?php

namespace App\Http\Controllers;


use App\Models\RequestStudent;
use Illuminate\Http\Request;

class RequestStudentController extends Controller
{

    // Lấy toàn bộ danh sách RequestStudent kèm thông tin folder và student
    public function index()
    {
        $data = RequestStudent::with(['folder:id,name', 'student:student_code,name'])->get();

        // Format lại chỉ trả về cần thiết
        $formatted = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'folder_name' => $item->folder->name ?? null,
                'student_name' => $item->student->name ?? null,
                'folder_id' => $item->folder->name ?? null,
                'status' => $item->status,

                'student_code' => $item->student_code,
                'created_at' => $item->created_at,
            ];
        });

        return response()->json($formatted);
    }

    // Tạo mới một bản ghi
    public function store(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'student_code' => 'required|exists:students,student_code',
            'status' => 'string',
        ]);

        $requestStudent = RequestStudent::create([
            'folder_id' => $request->folder_id,
            'student_code' => $request->student_code,
            'status' => $request->status,
        ]);

        return response()->json($requestStudent, 201);
    }

    // Lấy chi tiết một bản ghi theo ID
    public function show($id)
    {
        $requestStudent = RequestStudent::with(['folder', 'student'])->findOrFail($id);
        return response()->json($requestStudent);
    }

    // Cập nhật thông tin bản ghi
    public function update(Request $request, $id)
    {
        $requestStudent = RequestStudent::findOrFail($id);

        $request->validate([
            'folder_id' => 'sometimes|exists:folders,id',
            'student_code' => 'sometimes|exists:students,student_code',
            'status' => 'sometimes|string' //
        ]);

        $requestStudent->update($request->only(['folder_id', 'student_code']));

        return response()->json($requestStudent);
    }

    // Xóa bản ghi
    public function destroy($id)
    {
        $requestStudent = RequestStudent::findOrFail($id);
        $requestStudent->delete();

        return response()->json(['message' => 'Đã xoá thành công']);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:request_students,id',
            'status' => 'required|string|max:255',
        ]);

        RequestStudent::whereIn('id', $request->ids)->update([
            'status' => $request->status
        ]);

        return response()->json(['message' => 'Cập nhật trạng thái thành công']);
    }

    public function searchByStudentCode($student_code)
    {
        $studentCode = $student_code;

        $results = RequestStudent::where('student_code', 'like', "%$studentCode%")->with(['folder', 'student'])->get();

        return response()->json($results);
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

    public function getAllByStudentCode($studentCode)
    {
        $data = \App\Models\RequestStudent::where('student_code', $studentCode)
            ->with([
                'formRequest.values' => function ($q) use ($studentCode) {
                    $q->where('student_code', $studentCode)->with('field');
                },
                'formRequest.formType.folder'
            ])
            ->get();

        return response()->json($data, 200);
    }
}
