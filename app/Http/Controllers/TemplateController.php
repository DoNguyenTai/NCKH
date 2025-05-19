<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $template = Template::with("items")->get();
        return  $template;
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
        'name' => 'required|max:255',
        'items' => 'required|array',
    ], [
        'name.required' => 'Không được bỏ trống',
        'items.required' => 'Vui lòng thêm ít nhất một thành phần'
    ]);

    $template = Template::create([
        'name' => $validated['name'],
    ]);

    foreach ($validated['items'] as $itemData) {
        $template->items()->create([
            'type' => $itemData['type'] ?? null,
            'left' => $itemData['left'] ?? null,
            'top' => $itemData['top'] ?? null,
            'width' => $itemData['width'] ?? null,
            'height' => $itemData['height'] ?? null,
            'class_name' => $itemData['class_name'] ?? null,
            'value' => $itemData['value'] ?? null,
            'data' => $itemData['data'] ?? null,
            'rows' => $itemData['rows'] ?? null,
            'columns' => $itemData['columns'] ?? null,
            'column_ratios' => $itemData['column_ratios'] ?? null,
            'nested_config' => $itemData['nested_config'] ?? null,
        ]);
    }

    return response()->json(['message' => 'Thành công']);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $template= Template::with("items")->find($id);
        return $template;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
