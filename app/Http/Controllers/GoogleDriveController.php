<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Drive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Http;
use ZipArchive; // Import ZipArchive
use Carbon\Carbon; // Import Carbon (nếu bạn sử dụng Carbon::now())
use Illuminate\Support\Str;
class GoogleDriveController extends Controller
{
    private function getClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.clientId'));
        $client->setClientSecret(config('services.google.clientSecret'));
        $client->setRedirectUri(config('services.google.redirectUri'));
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setScopes([
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.metadata.readonly',
        ]);

        return $client;
    }

    public function redirectToGoogleAuth()
    {
        $client = $this->getClient();
        return redirect()->away($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = $this->getClient();
        $code = $request->input('code');

        if (!$code) {
            return response()->json(['error' => 'Missing authorization code'], 400);
        }

        try {
            $token = $client->fetchAccessTokenWithAuthCode($code);
            if (isset($token['error'])) {
                return response()->json(['error' => $token['error']], 400);
            }

            Storage::disk('local')->put('google-token.json', json_encode($token));
            return response()->json(['message' => 'Token saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getAuthenticatedClient(): ?Google_Client
    {
        $client = $this->getClient();

        if (!Storage::disk('local')->exists('google-token.json')) {
            return null;
        }

        $token = json_decode(Storage::disk('local')->get('google-token.json'), true);
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            if (!isset($token['refresh_token'])) {
                return null;
            }

            $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
            Storage::disk('local')->put('google-token.json', json_encode($client->getAccessToken()));
        }

        return $client;
    }

    public function listFiles()
    {
        $client = $this->getAuthenticatedClient();
        if (!$client) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            $driveService = new Google_Service_Drive($client);
            $files = $driveService->files->listFiles([
                'pageSize' => 10,
                'fields' => 'files(id, name, mimeType)',
            ]);

            return response()->json($files->getFiles());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadPdf(Request $request)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            $fileId = $request->input('fileId');
            $drive = new Google_Service_Drive($client);
            $response = $drive->files->export($fileId, 'application/pdf', ['alt' => 'media']);
            return response($response->getBody(), 200, [
                'Content-Type' => 'application/pdf'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function uploadDocxToDrive(Request $request)
    {
        $relativePath = 'public/document.docx';

        if (!Storage::disk('local')->exists($relativePath)) {
            return response()->json([
                'error' => "Không tìm thấy file output.docx trong storage/app/generated/"
            ], 500);
        }

        $client = $this->getAuthenticatedClient();
        if (!$client) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            $driveService = new \Google_Service_Drive($client);

            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => 'Generated_' . time() . '.docx',
                'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                // 'parents' => ['YOUR_FOLDER_ID'] // nếu muốn upload vào thư mục cụ thể
            ]);

            // Lấy nội dung file từ Storage
            $fileContents = Storage::disk('local')->get($relativePath);

            $uploadedFile = $driveService->files->create($fileMetadata, [
                'data' => $fileContents,
                'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'uploadType' => 'multipart',
                'fields' => 'id,webViewLink'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File đã được upload lên Google Drive.',
                'googleDocsPrintUrl' => $uploadedFile->webViewLink
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi upload lên Google Drive.',
                'details' => $e->getMessage()
            ], 500);
        } finally {
            // Xóa file nếu cần
            Storage::disk('local')->delete($relativePath);
        }
    }



    public function generateDocx(Request $request)
    {
        $data = $request->all(); // Lấy toàn bộ input vào mảng

        foreach ($data as $key => $value) {
            \Log::info("Key: $key - Value: $value");
            // Xử lý từng key-value ở đây
        }
        $data = [
            'name' => 'Nguyễn Văn A',
            'mssv' => 'MSV0012345',
            'ngay' => '10',
            'thang' => '07',
            'nam' => '2025',
            'lop' => '12A1'
        ];

        $templatePath = storage_path('app/public/document.docx');
        $outputPath = storage_path('app/public/output_' . time() . '.docx');

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        foreach ($data as $key => $value) {
            \Log::info("Thay {$key} => {$value}");
            $templateProcessor->setValue($key, $value);
        }
        $templateProcessor->saveAs($outputPath);
        if (file_exists($outputPath)) {
            \Log::info("✅ File đã được tạo tại: {$outputPath}");
        } else {
            \Log::warning("❌ Không thấy file output: {$outputPath}");
        }

        return response()->download($outputPath);
    }


    // private function generateDocxToPath(array $data): ?string
    // {
    //     $templatePath = storage_path('app/public/document.docx');
    //     if (!file_exists($templatePath)) {
    //         \Log::error("Không tìm thấy file template: $templatePath");
    //         return null;
    //     }

    //     $outputDir = storage_path('app/generated');
    //     if (!file_exists($outputDir)) {
    //         mkdir($outputDir, 0755, true);
    //     }

    //     $outputPath = $outputDir . '/output_' . time() . '.docx';
    //     $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

    //     foreach ($data as $key => $value) {
    //         $templateProcessor->setValue($key, $value);
    //     }

    //     $templateProcessor->saveAs($outputPath);
    //     return $outputPath;
    // }




    private function uploadDocxToDriveFromPath(string $filePath)
    {
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Không tìm thấy file để upload'], 500);
        }

        $client = $this->getAuthenticatedClient();
        if (!$client) {
            return response()->json(['error' => 'Chưa xác thực Google'], 401);
        }

        try {
            $driveService = new \Google_Service_Drive($client);

            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => 'Generated_' . time() . '.docx',
                'mimeType' => 'application/vnd.google-apps.document'
                // 'parents' => ['YOUR_FOLDER_ID'] // nếu muốn upload vào thư mục cụ thể
            ]);

            // Lấy nội dung file từ Storage
            $fileContents = file_get_contents($filePath);
            \Log::info($fileContents);
            $uploadedFile = $driveService->files->create($fileMetadata, [
                'data' => $fileContents,
                'mimeType' => 'application/vnd.google-apps.document',
                'uploadType' => 'multipart',
                'fields' => 'id,webViewLink'
            ]);
            // Xóa file tạm nếu cần
            // unlink($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Đã upload file lên Google Drive',
                'url' => $uploadedFile->webViewLink,
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi upload: ' . $e->getMessage());
            return response()->json(['error' => 'Upload thất bại', 'details' => $e->getMessage()], 500);
        }
    }



    public function generateThenUpload($formRequestId)
    {
        // B1: Lấy thông tin form + values
        $form = FormRequest::where('id', $formRequestId)
            // ->whereHas('values', function ($query) use ($studentCode) {
            //     $query->where('student_code', $studentCode);
            // })
            ->with([
                'values.field',
                'formType.folder'
            ])
            ->first();
            \Log::info($form->toArray());
        if (!$form) {
            return response()->json(['error' => 'Không tìm thấy biểu mẫu'], 404);
        }
        \Log::info($form);
        // B2: Tạo mảng $data: ['key' => value]
        $data = [];
        foreach ($form->values as $value) {
            $key = $value->field->key ?? null;
            if ($key) {
                $data[$key] = $value->value;
            }
        }
        \Log::info($form->formType->form_model);
        $templateFile = $form->formType->form_model ?? null;

        if (!$templateFile) {
            return response()->json(['error' => 'Không có template file'], 400);
        }

        $templatePath = storage_path('app/public/documents/' . $templateFile);
        if (!file_exists($templatePath)) {
            return response()->json(['error' => 'Không tìm thấy file mẫu: ' . $templatePath], 404);
        }

        // B4: Generate và upload
        $filePath = $this->generateDocxToPathWithTemplate($data, $templatePath);
        if (!$filePath) {
            return response()->json(['error' => 'Tạo file thất bại'], 500);
        }
        \Log::info($filePath);
        return $this->uploadDocxToDriveFromPath($filePath);
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



    public function exportHtml(Request $request)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            $fileId = $request->input('fileId');
            if (empty($fileId)) {
                return response()->json(['error' => 'Missing fileId parameter'], 400);
            }

            $driveService = new Google_Service_Drive($client);

            // Sử dụng Files::export để lấy nội dung HTML
            // 'text/html' là MIME type cho HTML
            $response = $driveService->files->export($fileId, 'text/html', ['alt' => 'media']);

            // Trả về nội dung HTML trực tiếp
            return response($response->getBody(), 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'inline; filename="exported_document.html"', // Gợi ý tên file khi lưu
            ]);
        } catch (\Google\Service\Exception $e) {
            // Xử lý các lỗi cụ thể từ Google API
            Log::error('Google Drive API Error (exportHtml): ' . $e->getMessage());
            return response()->json(['error' => 'Google Drive API error: ' . $e->getMessage()], $e->getCode());
        } catch (\Exception $e) {
            // Xử lý các lỗi chung khác
            Log::error('General Error (exportHtml): ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }



    public function uploadDocxFromClient(Request $request)
    {
        if (!$request->hasFile('docx_file')) {
            return response()->json(['error' => 'Chưa chọn file'], 400);
        }

        $file = $request->file('docx_file');

        if (!$file->isValid()) {
            return response()->json(['error' => 'File không hợp lệ'], 400);
        }

        $client = $this->getAuthenticatedClient(); // Hàm bạn đã viết để lấy Google_Client
        if (!$client) {
            return response()->json(['error' => 'Chưa xác thực Google'], 401);
        }

        try {
            $driveService = new \Google_Service_Drive($client);

            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $file->getClientOriginalName(), // Tên gốc
                'mimeType' => 'application/vnd.google-apps.document'
            ]);

            $uploadedFile = $driveService->files->create($fileMetadata, [
                'data' => file_get_contents($file->getRealPath()),
                'mimeType' => 'application/vnd.google-apps.document',
                'uploadType' => 'multipart',
                'fields' => 'id,webViewLink'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã upload file lên Google Drive',
                'url' => $uploadedFile->webViewLink,
            ]);
        } catch (\Exception $e) {
            \Log::error('Upload thất bại: ' . $e->getMessage());
            return response()->json(['error' => 'Upload thất bại', 'details' => $e->getMessage()], 500);
        }
    }

   public function exportPdfUrl(Request $request)
{
    $client = $this->getAuthenticatedClient();
    if (!$client) {
        return response()->json(['error' => 'Authentication required'], 401);
    }

    try {
        $fileId = $request->input('fileId');
        if (empty($fileId)) {
            return response()->json(['error' => 'Missing fileId parameter'], 400);
        }

        $service = new \Google_Service_Drive($client);
        $file = $service->files->get($fileId, ['fields' => 'mimeType, name']);

        $mimeType = $file->getMimeType();
        $exportableTypes = [
            'application/vnd.google-apps.document',
            'application/vnd.google-apps.spreadsheet',
            'application/vnd.google-apps.presentation',
        ];

        if (!in_array($mimeType, $exportableTypes)) {
            return response()->json(['error' => 'This file type cannot be exported to PDF'], 400);
        }

        // Cấp quyền công khai để có thể export
        $permission = new \Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);
        $service->permissions->create($fileId, $permission, ['fields' => 'id']);

        // Tạo link export PDF
        $pdfUrl = "https://docs.google.com/document/d/{$fileId}/export?format=pdf";
        $fileName = Str::slug($file->getName()) . '.pdf';

        // Tải file PDF từ Google Drive
        $response = Http::withOptions(['verify' => false])->get($pdfUrl);

        if (!$response->ok()) {
            return response()->json(['error' => 'Failed to download PDF'], 500);
        }

        // Lưu file vào storage/app/public/pdfs
        $path = 'pdfs/' . $fileName;
        Storage::disk('public')->put($path, $response->body());

        // Trả về link Google Drive và link tải từ server
        return response()->json([
            'pdf_url' => $pdfUrl,
            'file_name' => $fileName,
            // 'download_url' => asset('storage/' . $path),
        ]);
    } catch (\Exception $e) {
        \Log::error('exportPdfUrl error: ' . $e->getMessage());
        return response()->json(['error' => 'Unexpected error: ' . $e->getMessage()], 500);
    }
}
}
