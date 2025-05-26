<?php

namespace App\Http\Controllers;

use App\Models\RequestType;
use Illuminate\Http\Request;

class RequestTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(RequestType::all());
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $type = RequestType::create($data);
        return response()->json($type, 201);
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $type = RequestType::findOrFail($id);
        return response()->json($type);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestType $request_Type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $type = RequestType::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $type->update($data);
        return response()->json($type);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $type = RequestType::findOrFail($id);
        $type->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        if (!$keyword) {
            return response()->json(['message' => 'Keyword is required'], 400);
        }

        $types = RequestType::where('name', 'like', '%' . $keyword . '%')->get();
        return response()->json($types);
    }
}
