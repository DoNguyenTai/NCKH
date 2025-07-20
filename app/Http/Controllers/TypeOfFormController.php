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
            'parent_id' => 'nullable|exists:folders,id'
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

    public function getPdfUrl($id)
    {
        $form = TypeOfForm::findOrFail($id);

        if (!$form->pdf) {
            return response()->json(['error' => 'PDF not found'], 404);
        }

        $url = asset('storage/pdfs/' . $form->pdf);

        return response()->json(['url' => $url]);
    }

    // Trả về URL download file Word
  public function getWordUrl($id)
{
    $form = TypeOfForm::findOrFail($id);

    if (!$form->word) {
        return response()->json(['error' => 'Word file not found'], 404);
    }

    $filePath = storage_path('app/public/original/' . $form->word);

    if (!file_exists($filePath)) {
        return response()->json(['error' => 'File does not exist'], 404);
    }

    return response()->download($filePath, $form->word);
}
}
