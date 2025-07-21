<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FieldForm;
use App\Models\FormRequest;
use App\Models\FormRequestValue;
use App\Models\TypeOfForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        \Log::info('test' . $request);
        // Lưu file Word
        $file = $request->file('doc_file');
        if (!$file) {
            return response()->json(['error' => 'Missing Word file'], 400);
        }

        $docFileName = 'doc_' . time() . '_' . $file->getClientOriginalName();
        $docPath = $file->storeAs('public/original', $docFileName);
        $fullDocPath = storage_path('app/' . $docPath);

        // Tải PDF từ URL
        $pdfUrl = $request->input('url_pdf');
        if (!$pdfUrl) {
            return response()->json(['error' => 'Missing url_pdf'], 400);
        }

        $response = Http::withOptions(['verify' => false])->get($pdfUrl);
        if (!$response->ok()) {
            \Log::error("Failed to download PDF from: $pdfUrl");
            return response()->json(['error' => 'Failed to download PDF'], 500);
        }

        $pdfFileName = 'pdf_' . time() . '_' . Str::random(6) . '.pdf';
        $pdfPath = 'pdfs/' . $pdfFileName;
        Storage::disk('public')->put($pdfPath, $response->body());
        \Log::info("PDF saved to: $pdfPath");

        // Cập nhật thông tin file trong bảng type_of_forms
        $typeofform = TypeOfForm::find($formId);
        if ($typeofform) {
            $typeofform->pdf = $pdfFileName;
            $typeofform->word = $docFileName;
            $typeofform->save();
        }

        // Thêm các field vào form
        $maxOrder = FieldForm::where('form_id', $formId)->max('order') ?? 0;
        $fields = json_decode($request->input('fields'), true);

        foreach ($fields as $field) {
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
                'key' => $field['key'],
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
        // 1. Xác thực dữ liệu đầu vào (Validation)
        // Đây là bước quan trọng nhất. Bạn có thể tạo một Form Request riêng
        // hoặc dùng Validator::make() trực tiếp ở đây.
        $validator = Validator::make($request->all(), [
            'student_code' => [
                'required',
                'string',
                // Đảm bảo đúng 8 chữ số
                // 'exists:students,code', // Giả sử cột MSSV trong bảng 'students' là 'code'
                // Bỏ comment dòng này nếu bạn có bảng students và muốn kiểm tra sự tồn tại
            ],
            'values' => 'required|array', // 'values' phải là một mảng
            // Thêm các quy tắc xác thực cho từng trường cụ thể nếu cần
            // Ví dụ: 'values.*.field_id' => 'required|integer|exists:field_forms,id',
            // 'values.*.value' => 'required|string|max:255',
        ], [
            'student_code.required' => 'Vui lòng nhập mã số sinh viên.',
            'student_code.string' => 'Mã số sinh viên phải là chuỗi.',
            'student_code.exists' => 'Mã số sinh viên không tồn tại.',
            'values.required' => 'Không có giá trị biểu mẫu nào được gửi.',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422); // 422 Unprocessable Entity
        }

        $studentCode = $request->input('student_code');
        $inputValues = $request->input('values', []);

        // Kiểm tra sự tồn tại của MSSV nếu chưa dùng 'exists' rule
        // if (!Student::where('code', $studentCode)->exists()) {
        //     return response()->json(['message' => 'Mã số sinh viên không tồn tại.'], 404);
        // }

        // Tìm biểu mẫu
        try {
            $form = TypeOfForm::with('fieldForm')->findOrFail($formId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Biểu mẫu không tìm thấy.'], 404);
        }

        // 2. Sử dụng Database Transaction để đảm bảo tính nguyên tử
        DB::beginTransaction();
        try {
            // Tạo bản ghi gửi biểu mẫu chính
            $submission = FormRequest::create([
                'type_of_form_id' => $formId,
                // Thêm student_code vào FormRequest nếu cần để dễ truy vấn
                'student_code' => $studentCode,
            ]);

            $formRequestValues = [];
            foreach ($form->fieldForm as $field) {
                $fieldKey = $field->id;

                // Lấy giá trị từ inputValues, nếu không có thì mặc định là null hoặc chuỗi rỗng
                // Tùy thuộc vào yêu cầu của từng trường (có thể kiểm tra 'required' ở validation)
                $value = $inputValues[$fieldKey] ?? null;

                // Nếu bạn muốn chỉ lưu các trường có giá trị được gửi lên
                if ($value !== null) { // Hoặc dùng !empty($value) tùy vào logic của bạn
                    $formRequestValues[] = [
                        'form_request_id' => $submission->id,
                        'field_form_id' => $field->id,
                        'student_code' => $studentCode, // Nên lưu student_code ở đây để dễ truy vấn
                        'value' => $value,
                        'created_at' => now(), // Thêm timestamps thủ công cho bulk insert
                        'updated_at' => now(),
                    ];
                }
            }

            // 3. Tối ưu hiệu suất với Bulk Insert
            if (!empty($formRequestValues)) {
                FormRequestValue::insert($formRequestValues);
            }

            DB::commit(); // Hoàn tất giao dịch

            return response()->json(['message' => 'Gửi biểu mẫu thành công!']);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác giao dịch nếu có lỗi
            \Log::error("Lỗi khi gửi biểu mẫu: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Đã xảy ra lỗi khi xử lý biểu mẫu. Vui lòng thử lại sau.'], 500);
        }
    }


    public function previewForm($id)
    {
        $data = FormRequest::with('values')->find($id);
        return response()->json($data, 200);
    }
    public function getAllFormValue()
    {
        $data = FormRequestValue::with([
            'submission', // Truy lên formRequest rồi truy tiếp lên formType
            'student',
            'field'
        ])->get();

        return response()->json($data, 200);
    }
    public function getAllFormValueByTemplate($studentCode)
    {
        $data = FormRequest::whereHas('values', function ($q) use ($studentCode) {
            $q->where('student_code', $studentCode);
        })
            ->with([
                'values' => function ($query) use ($studentCode) {
                    $query->where('student_code', $studentCode);
                },
                // 'values.field',   // load quan hệ field cho từng value
                'formType.folder'        // load loại biểu mẫu
            ])
            ->get();

        return response()->json($data, 200);
    }
    public function getAllFormValueByTemplateByFolder($studentCode, $folderId)
    {
        $data = FormRequest::whereHas('values', function ($q) use ($studentCode) {
            $q->where('student_code', $studentCode);
        })
            ->whereHas('formType.folder', function ($q) use ($folderId) {
                $q->where('id', $folderId);
            })
            ->with([
                'values' => function ($query) use ($studentCode) {
                    $query->where('student_code', $studentCode)->with('field');
                },
                'formType.folder' // chỉ load mà không lọc trong eager
            ])
            ->get();

        return response()->json($data, 200);
    }
    public function getAllFormValueByTemplateByFolderWithDate($studentCode, $folderId, $createdAt)
    {
        $query = FormRequest::whereHas('values', function ($q) use ($studentCode, $createdAt) {
            $q->where('student_code', $studentCode);

            // Lọc theo ngày trong bảng values
            if ($createdAt) {
                $q->whereDate('created_at', $createdAt);
            }
        })
            ->whereHas('formType.folder', function ($q) use ($folderId) {
                $q->where('id', $folderId);
            });

        $data = $query->with([
            'values' => function ($q) use ($studentCode, $createdAt) {
                $q->where('student_code', $studentCode)
                    ->when($createdAt, function ($subQuery) use ($createdAt) {
                        $subQuery->whereDate('created_at', $createdAt);
                    })
                    ->with('field');
            },
            'formType.folder'
        ])->get();

        return response()->json($data, 200);
    }



    public function getFormValueDetail($studentCode, $formRequestId)
    {
        $form = FormRequest::where('id', $formRequestId)
            ->whereHas('values', function ($query) use ($studentCode) {
                $query->where('student_code', $studentCode);
            })
            ->with([
                'values' => function ($query) use ($studentCode) {
                    $query->where('student_code', $studentCode);
                },
                'values.field',
                'formType.folder'
            ])
            ->first();

        if (!$form) {
            return response()->json(['message' => 'Không tìm thấy dữ liệu'], 404);
        }

        return response()->json($form, 200);
    }
}
