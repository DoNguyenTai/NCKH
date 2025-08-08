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
        // Tìm FormRequest một lần duy nhất
        $formRequest = FormRequest::with(['values.field', 'formType.folder'])->find($formRequestId);

        if (!$formRequest) {
            return response()->json(['error' => 'Không tìm thấy biểu mẫu'], 404);
        }

        // Xử lý dữ liệu (giữ nguyên)
        $data = [];
        foreach ($formRequest->values as $value) {
            if ($key = $value->field->key) {
                $data[$key] = $value->value;
            }
        }

        // Kiểm tra file mẫu (giữ nguyên)
        $templateFile = $formRequest->formType->form_model ?? null;
        if (!$templateFile) {
            return response()->json(['error' => 'Không có template file'], 400);
        }
        $templatePath = storage_path('app/public/documents/' . $templateFile);
        if (!file_exists($templatePath)) {
            return response()->json(['error' => 'Không tìm thấy file mẫu: ' . $templatePath], 404);
        }

        // Tạo file tạm (giữ nguyên)
        $tempFilePath = $this->generateDocxToPathWithTemplate($data, $templatePath);
        if (!$tempFilePath) {
            return response()->json(['error' => 'Tạo file thất bại'], 500);
        }

        // === PHẦN SỬA LỖI QUAN TRỌNG ===
        $filename = basename($tempFilePath);
        $fileContent = file_get_contents($tempFilePath);

        // 1. Chỉ định rõ ràng lưu vào disk 'public'
        //    Đường dẫn bây giờ chỉ cần là 'generated/filename.docx'
        Storage::disk('public')->put('generated/' . $filename, $fileContent);

        // 2. Lấy URL công khai một cách chính xác
        $downloadUrl = Storage::disk('public')->url('generated/' . $filename);

        // Xóa file tạm sau khi đã lưu
        unlink($tempFilePath);
        // ===============================

        // Cập nhật tên file vào database
        $formRequest->file_docx = $filename;
        $formRequest->save();

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
        // 1. Luôn sử dụng 'public' disk để làm việc với các file công khai
        $disk = Storage::disk('public');
        $path = 'generated/' . $filename;

        // 2. Kiểm tra file có tồn tại trên 'public' disk không
        if (!$disk->exists($path)) {
            \Log::error('File not found on public disk: ' . $path);
            return response()->json(['error' => 'File not found or not accessible'], 404);
        }

        // 3. Lấy URL công khai chính xác thông qua Storage facade
        //    Hàm url() sẽ tự động tạo đường dẫn đúng, ví dụ: /storage/generated/file.docx
        return response()->json([
            'url' => $disk->url($path)
        ]);
    }
}
