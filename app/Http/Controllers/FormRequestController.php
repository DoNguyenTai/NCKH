<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        Log::info('--- Bắt đầu quá trình tạo file DOCX cho request ID: ' . $formRequestId . ' ---');
        try {
            // Tìm FormRequest một lần duy nhất
            $formRequest = FormRequest::with(['values.field', 'formType.folder'])->find($formRequestId);

            if (!$formRequest) {
                Log::warning('Không tìm thấy FormRequest với ID: ' . $formRequestId);
                return response()->json(['error' => 'Không tìm thấy biểu mẫu'], 404);
            }

            // Xử lý dữ liệu
            $data = [];
            foreach ($formRequest->values as $value) {
                if ($key = $value->field->key) {
                    $data[$key] = $value->value;
                }
            }
            Log::info('Đã xử lý xong dữ liệu từ form.');

            // Kiểm tra file mẫu
            $templateFile = $formRequest->formType->form_model ?? null;
            if (!$templateFile) {
                Log::error('form_model is null cho FormType ID: ' . $formRequest->formType->id);
                return response()->json(['error' => 'Không có template file'], 400);
            }
            $templatePath = storage_path('app/public/documents/' . $templateFile);
            if (!file_exists($templatePath)) {
                Log::error('Không tìm thấy file mẫu tại đường dẫn: ' . $templatePath);
                return response()->json(['error' => 'Không tìm thấy file mẫu: ' . $templateFile], 404);
            }
            Log::info('Đã tìm thấy file mẫu: ' . $templatePath);

            // Tạo file tạm
            $tempFilePath = $this->generateDocxToPathWithTemplate($data, $templatePath);

            if (!$tempFilePath || !file_exists($tempFilePath)) {
                Log::error('Hàm generateDocxToPathWithTemplate không trả về đường dẫn hợp lệ hoặc file tạm không tồn tại.');
                return response()->json(['error' => 'Tạo file tạm thất bại'], 500);
            }
            Log::info('File tạm đã được tạo thành công tại: ' . $tempFilePath);

            // Đọc nội dung file tạm
            $filename = basename($tempFilePath);
            $fileContent = file_get_contents($tempFilePath);
            Log::info('Đã đọc nội dung file tạm. Kích thước: ' . strlen($fileContent) . ' bytes.');

            // === BƯỚC QUAN TRỌNG: LƯU FILE VÀO STORAGE ===
            $storagePath = 'generated/' . $filename;
            Log::info('Chuẩn bị lưu file vào Storage disk "public" tại đường dẫn: ' . $storagePath);

            $success = Storage::disk('public')->put($storagePath, $fileContent);

            if ($success) {
                Log::info('✅ LƯU FILE THÀNH CÔNG! Storage::put trả về true.');
            } else {
                // Đây là lỗi nghiêm trọng nhất
                Log::error('❌ LƯU FILE THẤT BẠI! Storage::put trả về false. Vấn đề có thể là do permissions hoặc cấu hình Volume trên Railway.');
                return response()->json(['error' => 'Không thể lưu file vào storage.'], 500);
            }

            // Lấy URL và ghi log
            $downloadUrl = Storage::disk('public')->url($storagePath);
            Log::info('Đã tạo URL công khai: ' . $downloadUrl);

            // Xóa file tạm sau khi đã lưu
            // unlink($tempFilePath);
            Log::info('Đã xóa file tạm: ' . $tempFilePath);

            // Cập nhật tên file vào database
            $formRequest->file_docx = $filename;
            $formRequest->save();
            Log::info('Đã cập nhật tên file vào database thành công.');

            Log::info('--- Hoàn tất quá trình tạo file DOCX. ---');
            return response()->json([
                'message' => 'Tạo file thành công',
                'url' => $downloadUrl
            ]);
        } catch (\Exception $e) {
            // Bắt tất cả các lỗi ngoại lệ có thể xảy ra
            Log::error('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
            Log::error('!!! ĐÃ XẢY RA LỖI NGOẠI LỆ TRONG QUÁ TRÌNH !!!');
            Log::error('Lỗi: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' - Dòng: ' . $e->getLine());
            Log::error('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
            return response()->json(['error' => 'Đã có lỗi nghiêm trọng xảy ra. Vui lòng kiểm tra logs.'], 500);
        }
    }
    private function generateDocxToPathWithTemplate(array $data, string $templatePath): ?string
    {
        if (!file_exists($templatePath)) {
            \Log::error("Không tìm thấy file template: $templatePath");
            return null;
        }
        \Log::info($data);
        $outputDir = storage_path('app/public/generated');
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
