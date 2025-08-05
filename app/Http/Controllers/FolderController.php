<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index()
    {
        return Folder::all();
    }

    public function store(Request $request)
    {
        $folder = Folder::create($request->only('name', 'parent_id', 'is_folder'));
        return response()->json($folder, 201);
    }

    public function update(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);
        $folder->update($request->only('name'));
        return response()->json($folder);
    }

    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);
        $folder->delete();
        return response()->json(['message' => 'Deleted']);
    }
        public function show($id)
    {
        $folder = Folder::findOrFail($id);
       
        return response()->json($folder,200);
    }
}
