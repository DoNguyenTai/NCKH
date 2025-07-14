<?php

namespace App\Http\Controllers;

use App\Models\TypeOfForm;
use Illuminate\Http\Request;

class TypeOfFormController extends Controller
{
    // Lấy danh sách tất cả các form
    public function index()
    {
        return response()->json(TypeOfForm::all());
    }

    // Tạo mới một form
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'form_model' => 'nullable|string',
            'parent_id' => 'required|exists:folders,id',
        ]);

        $form = TypeOfForm::create($validated);

        return response()->json($form, 201);
    }

    // Xem chi tiết một form
    public function show($id)
    {
        $form = TypeOfForm::findOrFail($id);
        return response()->json($form);
    }

    // Cập nhật form
    public function update(Request $request, $id)
    {
        $form = TypeOfForm::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'form_model' => 'nullable|string',
            'parent_id' => 'sometimes|required|exists:folders,id',
        ]);

        $form->update($validated);

        return response()->json($form);
    }

    // Xóa form
    public function destroy($id)
    {
        $form = TypeOfForm::findOrFail($id);
        $form->delete();

        return response()->json(['message' => 'Form deleted successfully.']);
    }
}
