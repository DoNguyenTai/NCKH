<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FieldForm;
use App\Models\FormRequest;
use App\Models\FormRequestValue;
use App\Models\TypeOfForm;
use Illuminate\Http\Request;

class FormCustomController extends Controller
{
    public function getTypeOfForms()
    {
        return response()->json(TypeOfForm::all());
    }

    public function getFormWithFields($formId)
    {
        $form = TypeOfForm::with(['fieldForm' => function ($q) {
            $q->orderBy('order');
        }])->findOrFail($formId);

        return response()->json($form);
    }

    public function storeField(Request $request, $formId)
    {

        // $request->validate([
        //     'fields' => 'required|array|min:1',
        //     'fields.*.label' => 'required|string',
        //     'fields.*.key' => 'required|string',
        //     'fields.*.options' => 'nullable|array',
        // ]);
        $maxOrder = FieldForm::where('form_id', $formId)->max('order') ?? 0;

        foreach ($request->fields as $field) {
            $exists = FieldForm::where('form_id', $formId)
                ->where('label', $field['label'])
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'Tên trường đã tồn tại!'], 422);
            }

            if (in_array($field['data_type'], ['checkbox', 'radio']) && empty(array_filter($field['options'] ?? []))) {
                return response()->json(['message' => 'Checkbox hoặc Radio phải có ít nhất một lựa chọn.'], 422);
            }

            FieldForm::create([
                'form_id' => $formId,
                'label' => $field['label'],
                'data_type' => $field['data_type'],
                'options' => in_array($field['data_type'], ['checkbox', 'radio']) ? $field['options'] : null,
                'order' => ++$maxOrder,
            ]);
        }

        return response()->json(['message' => 'Thêm trường thành công.']);
    }

    public function updateField(Request $request, $formId, $fieldId)
    {
        $request->validate([
            'label' => 'required|string',
            'data_type' => 'required|string',
            'options' => 'nullable|array',
        ]);
        dd($formId);
        $field = FieldForm::where('form_id', $formId)->findOrFail($fieldId);
        // $dataField = [
        //     'label' => $request->label,
        //     'data_type' => $request->data_type,
        //     'options' => in_array($request->data_type, ['checkbox', 'radio']) ? json_encode($request->options ?? []) : null,
        // ];
        $field->update([
            'label' => $request->label,
            'data_type' => $request->data_type,
            'options' => in_array($request->data_type, ['checkbox', 'radio']) ? ($request->options ?? []) : null,
            // in_array($field['key'], ['checkbox', 'radio']) ? ($field['options'] ?? []) : null,
        ]);



        return response()->json(['message' => 'Cập nhật thành công.']);
    }

    public function deleteField($formId, $fieldId)
    {
        $field = FieldForm::where('form_id', $formId)->findOrFail($fieldId);
        $field->delete();

        return response()->json(['message' => 'Xoá thành công.']);
    }

    public function reorder(Request $request, $formId)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer',
        ]);

        foreach ($request->order as $index => $item) {
            FieldForm::where('form_id', $formId)
                ->where('id', $item['id'])
                ->update(['order' => $index + 1]);
        }

        return response()->json(['message' => 'Đã cập nhật thứ tự.']);
    }

    public function submitForm(Request $request, $formId)
    {
        // Lấy form từ DB
        $form = TypeOfForm::with('fieldForm')->findOrFail($formId);

        // Tạo bản ghi form submission
        $submission = FormRequest::create([
            'type_of_form_id' => $formId,
        ]);

        foreach ($form->fieldForm as $field) {
            $fieldKey = $field->id;

            if ($request->has($fieldKey)) {
                $value = $request->input($fieldKey);
                if (is_array($value)) {
                    $value = $value;
                }

                FormRequestValue::create([
                    'form_request_id' => $submission->id,
                    'field_form_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }

        return response()->json(['message' => 'Gửi biểu mẫu thành công!']);

    }

    public function previewForm($id)
{
    $data = FormRequest::with('values')->find($id);
    return response()->json($data, 200);
}
}
