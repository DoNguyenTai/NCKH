<?php

namespace App\Http\Controllers;

use App\Models\FieldForm;
use App\Models\TypeOfForm;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index()
    {
        $typeOfForm = TypeOfForm::all();
        return view('admin.index', ["typeOfForm" => $typeOfForm]);
    }
    public function store(Request $request)
    {
        $form_id = $request->input("type_of_form");
        $request->validate([
            'custom_fields' => 'array',
            'custom_fields.*.key' => 'required|string',
            'custom_fields.*.value' => 'string',
        ]);
        $field = $request->input("custom_fields");


        foreach ($field as $value) {
            $formData = FieldForm::where('form_id', $form_id)
                ->where('value', 'LIKE', "%{$value['value']}%")
                ->count();
            
            if ($formData === 0) {
                FieldForm::create([
                    'form_id' => $form_id,
                    'data_type' => $value['key'],
                    'value' => $value['value'],
                ]);
            } else {
                return redirect()->back()->with("error", "Trường đã tồn tại");
            }
        }
        return view('admin.show');
    }

    public function showForm($id)
    {
        $typeOfForm = TypeOfForm::with(['fieldForm' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->find($id);
        return view('form', ["typeOfForm" => $typeOfForm]);
    }

    public function updateOrder(Request $request) {
    $order = $request->all();
    foreach ($order['order'] as $value) {
        $fieldForm = FieldForm::find($value['id']);
        if(!empty($fieldForm)) {
            $fieldForm->update(['order' => $value['position']]);
        }
    }
    }
}
