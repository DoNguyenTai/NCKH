<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use Illuminate\Http\Request;

class FormRequestController extends Controller
{
    
    public function index()
    {
        $formRequests = FormRequest::with('formType.folder','values')->get();
        return response()->json($formRequests);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_of_form_id' => 'required|exists:type_of_forms,id',
            'url_docx' => 'nullable|string',
            'file_docx' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $formRequest = FormRequest::create($request->all());

        return response()->json([
            'message' => 'Tạo form request thành công.',
            'data' => $formRequest
        ], 201);
    }

    public function show($id)
    {
        $formRequest = FormRequest::with('typeOfForm')->find($id);

        if (!$formRequest) {
            return response()->json(['message' => 'Không tìm thấy form request.'], 404);
        }

        return response()->json($formRequest);
    }

    public function update(Request $request, $id)
    {
        $formRequest = FormRequest::find($id);

        if (!$formRequest) {
            return response()->json(['message' => 'Không tìm thấy form request.'], 404);
        }

        $request->validate([
            'type_of_form_id' => 'sometimes|exists:type_of_forms,id',
            'url_docx' => 'nullable|string',
            'file_docx' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $formRequest->update($request->all());

        return response()->json([
            'message' => 'Cập nhật form request thành công.',
            'data' => $formRequest
        ]);
    }

    public function destroy($id)
    {
        $formRequest = FormRequest::find($id);

        if (!$formRequest) {
            return response()->json(['message' => 'Không tìm thấy form request.'], 404);
        }

        $formRequest->delete();

        return response()->json(['message' => 'Xóa form request thành công.']);
    }
}
