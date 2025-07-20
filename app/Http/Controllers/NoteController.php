<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    // Lấy danh sách tất cả ghi chú
    public function index()
    {
        return response()->json(Note::all(), 200);
    }

    // Lưu ghi chú mới
    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => 'required|string',
            'parent_id' => 'nullable|exists:folders,id',

        ]);

        $note = Note::create($validated);

        return response()->json($note, 201);
    }

    // Hiển thị chi tiết 1 ghi chú
    public function show(Note $note)
    {
        return response()->json($note, 200);
    }

    // Cập nhật ghi chú
    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'content' => 'sometimes|required|string',
            'name' => 'sometimes|required|string',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $note->update($validated);

        return response()->json($note, 200);
    }

    // Xóa ghi chú
    public function destroy(Note $note)
    {
        $note->delete();

        return response()->json(['message' => 'Đã xóa ghi chú'], 200);
    }
    public function showIdFolder($folder_id)
    {
        $note =  Note::where("parent_id", $folder_id)->get();
        if ($note->isEmpty()) {
            return response()->json(['message' => 'No notes found'], 404);
        }
        return response()->json($note, 200);
    }
}
