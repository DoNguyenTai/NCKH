<?php

namespace App\Http\Controllers;

use App\Models\DependencyForm;
use App\Models\FieldForm;
use App\Models\FormRequest;
use App\Models\FormRequestValue;
use App\Models\TypeOfForm;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function showFieldForm($id)
    {
        $typeOfForm = TypeOfForm::with(['fieldForm' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->find($id);
        return view('form', ["typeOfForm" => $typeOfForm]);
    }
    // public function showForm($id)
    // {
    //     $typeOfForm = TypeOfForm::with(['fieldForm' => function ($query) {
    //         $query->orderBy('order', 'asc');
    //     }])->find($id);
    //     return response()->json($typeOfForm, 200);
    // }

    public function updateOrder(Request $request)
    {
        $order = $request->all();
        foreach ($order['order'] as $value) {
            $fieldForm = FieldForm::find($value['id']);
            if (!empty($fieldForm)) {
                $fieldForm->update(['order' => $value['position']]);
            }
        }
    }

    public function submitForm(Request $request, $formId)
    {
        // Lấy form từ DB

        $form = TypeOfForm::with('fieldForm')->findOrFail($formId);
        // Tạo bản ghi form submission
        $submission = FormRequest::create([
            'type_of_form_id' => $formId,
        ]);

        // Lưu từng giá trị form vào form_submission_values
        foreach ($form->fieldForm as $field) {
            $fieldKey = 'field_' . $field->id;

            if ($request->has($fieldKey)) {
                $value = $request->input($fieldKey);

                // Nếu là mảng (checkbox...), encode lại để lưu vào cột JSON
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

        return back()->with('success', 'Gửi biểu mẫu thành công!');
    }


    public function viewForm()
    {
        return view('image_template');
    }

    public function storeFormModel(Request $request, $id)
    {
        // $form_name = $request->input("name");
        $form_model = $request->input("form-model");
        $form = TypeOfForm::find($id);
        $form->fieldForm()->delete();
        self::deleteUploadedDocx($form['form-model']);
        $data = $form->update([
            'form-model' => $form_model
        ]);
        return response()->json($data, 200);
    }

    public function showFormModel($id)
    {
        $data = TypeOfForm::find($id);
        return response()->json($data, 200);
    }

    public function storeForm(Request $request)
    {

        $form =  TypeOfForm::create([
            'name' => $request->name
        ]);
        return response()->json($form, 200);
    }

    public function deleteForm($id)
    {

        $form =  TypeOfForm::find($id);
        $form->fieldForm()->delete();
        $this->deleteUploadedDocx($form['form-model']);
        $form->delete();
        return response()->json($form, 200);
    }

    public function updateForm(Request $request, $id)
    {
        $form = TypeOfForm::find($id);
        $form->update([
            'name' => $request->name
        ]);
        return response()->json($form, 200);
    }

    public function statusForm()
    {
        $form = TypeOfForm::with('formRequest')->get();

        return response()->json($form, 200);
    }

    public function deleteUploadedDocx($filename)
    {
        $filePath = "public/documents/{$filename}";

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            return response()->json(['message' => 'Đã xóa file thành công']);
        }

        return response()->json(['message' => 'File không tồn tại'], 404);
    }

    public function dependencyForm(Request $request)
    {
        $formId = $request->input('form_id');
        $dependencyFormId = $request->input('dependency_form_id');
        DependencyForm::where('form_id', $formId)->delete();
        foreach ($dependencyFormId as $value) {
            DependencyForm::create([
                'form_id' => $formId,
                'dependency_form_id' => $value,
            ]);
        }
        return response()->json(['message' => 'Success'], 200);
    }

    public function getDependencyForms($formId)
    {
        $dependencies = DependencyForm::with('dependencyName')->where('form_id', $formId)->get();
        return response()->json([
            'form_id' => $formId,
            'dependencies' => $dependencies->map(function ($dep) {
                return [
                    'id' => $dep->dependency_form_id,
                    'name' => $dep->dependencyName->name ?? null,
                ];
            }),
        ]);
    }
}
