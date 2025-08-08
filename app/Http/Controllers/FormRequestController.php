<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormRequestController extends Controller
{

    public function index()
    {
        $formRequests = FormRequest::with('formType.folder', 'values',)->get();
        return response()->json($formRequests);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_of_form_id' => 'required|exists:type_of_forms,id',
            'url_docx' => 'nullable|string',
            'file_docx' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $formRequest = FormRequest::create($request->all());

        return response()->json([
            'message' => 'Tạo form request thành công.',
            'data' => $formRequest
        ], 201);
    }

    public function show($id)
    {
        $formRequest = FormRequest::find($id);

        if (!$formRequest) {
            return response()->json(['message' => 'Không tìm thấy form request.'], 404);
        }

        return response()->json($formRequest);
    }

    public function update(Request $request, $id)
    {
        $formRequest = FormRequest::find($id);

        if (!$formRequest) {
            return response()->json(['message' => 'Không tìm thấy form request.'], 404);
        }

        $request->validate([
            'type_of_form_id' => 'sometimes|exists:type_of_forms,id',
            'url_docx' => 'nullable|string',
            'file_docx' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $formRequest->update($request->all());

        return response()->json([
            'message' => 'Cập nhật form request thành công.',
            'data' => $formRequest
        ]);
    }

    public function destroy($id)
    {
        $formRequest = FormRequest::find($id);

        if (!$formRequest) {
            return response()->json(['message' => 'Không tìm thấy form request.'], 404);
        }

        $formRequest->delete();

        return response()->json(['message' => 'Xóa form request thành công.']);
    }



    public function generateThenUpload($formRequestId)
    {
        $form = FormRequest::where('id', $formRequestId)
            ->with(['values.field', 'formType.folder'])
            ->first();

        if (!$form) {
            return response()->json(['error' => 'Không tìm thấy biểu mẫu'], 404);
        }

        $data = [];
        foreach ($form->values as $value) {
            $key = $value->field->key ?? null;
            if ($key) {
                $data[$key] = $value->value;
            }
        }

        $templateFile = $form->formType->form_model ?? null;
        if (!$templateFile) {
            return response()->json(['error' => 'Không có template file'], 400);
        }

        $templatePath = storage_path('app/public/documents/' . $templateFile);
        if (!file_exists($templatePath)) {
            return response()->json(['error' => 'Không tìm thấy file mẫu: ' . $templatePath], 404);
        }

        // Hàm tạo file docx từ template (bạn có sẵn)
        $filePath = $this->generateDocxToPathWithTemplate($data, $templatePath);
        if (!$filePath) {
            return response()->json(['error' => 'Tạo file thất bại'], 500);
        }

        // Lưu file vào thư mục public/generated trực tiếp (không dùng storage link)
        $filename = basename($filePath);
        $fileContent = file_get_contents($filePath);

        // Đảm bảo thư mục public/generated tồn tại
        $publicDir = public_path('generated');
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $publicGeneratedPath = public_path('generated/' . $filename);
        file_put_contents($publicGeneratedPath, $fileContent);

        // Tạo URL truy cập file
        $downloadUrl = asset('generated/' . $filename);

        // Lưu tên file vào database
        $formRequest = FormRequest::find($formRequestId);
        if ($formRequest) {
            $formRequest->file_docx = $filename;
            $formRequest->save();
        }

        return response()->json([
            'message' => 'Tạo file thành công',
            'url' => $downloadUrl
        ]);
    }


    private function generateDocxToPathWithTemplate(array $data, string $templatePath): ?string
    {
        if (!file_exists($templatePath)) {
            \Log::error("Không tìm thấy file template: $templatePath");
            return null;
        }
        \Log::info($data);
        $outputDir = storage_path('app/generated');
        // if (!file_exists($outputDir)) {
        //     mkdir($outputDir, 0755, true);
        // }

        $outputPath = $outputDir . '/output_' . time() . '.docx';
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        foreach ($data as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        $templateProcessor->saveAs($outputPath);
        return $outputPath;
    }
    public function getDownloadUrlByFilename($filename)
    {
        $path = public_path('generated/' . $filename);
        \Log::info('Checking file at: ' . $path);

        if (!file_exists($path)) {
            \Log::warning('File not found: ' . $path);
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->json([
            'url' => asset('generated/' . $filename)
        ]);
    }
}
