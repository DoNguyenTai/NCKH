<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use DOMDocument;

use Illuminate\Support\Facades\Http;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Table;
use ZipArchive;

class DocxController extends Controller
{
    private $libreOfficeBinaryPath;

    public function __construct()
    {
        $this->libreOfficeBinaryPath = PHP_OS_FAMILY === 'Windows'
            ? 'C:\\Program Files\\LibreOffice\\program\\soffice.exe'
            : '/usr/bin/soffice';
    }
    public function uploadDocx(Request $request)
    {
        // Validate
        $request->validate([
            'doc_file' => 'required|file|max:10240', // max 10MB
        ]);

        // self::deleteUploadedDocx($filename);


        $file = $request->file('doc_file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // Lưu file
        $path = $file->storeAs('public/documents', $filename);
        $fullPath = storage_path('app/' . $path);

        // Đọc biến từ file
        $placeholders = $this->extractDocxPlaceholders($fullPath);

        return response()->json([
            'message' => 'Upload thành công',
            'filename' => $filename,
            'url' => asset(Storage::url($path)), // đường public
            'variables' => $placeholders,
        ]);
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('docx')) {
            return response()->json(['error' => 'Chưa có file upload'], 400);
        }

        $file = $request->file('docx');
        $uuid = Str::uuid();
        $docxFilename = "$uuid.docx";
        $htmlFilename = "$uuid.html";

        $outputDir = storage_path('app/temp');
        $docxPath = "$outputDir/$docxFilename";
        $htmlPath = "$outputDir/$htmlFilename";

        try {
            $file->storeAs('temp', $docxFilename);

            $cmd = escapeshellarg($this->libreOfficeBinaryPath) .
                " --headless --convert-to html " .
                escapeshellarg($docxPath) .
                " --outdir " . escapeshellarg($outputDir);

            exec($cmd, $output, $code);

            if ($code !== 0 || !file_exists($htmlPath) || filesize($htmlPath) === 0) {
                return response()->json(['error' => 'Chuyển đổi thất bại'], 500);
            }

            $htmlContent = file_get_contents($htmlPath);
            @$dom = new DOMDocument();
            @$dom->loadHTML($htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $css = '';
            $bodyHtml = '';

            $head = $dom->getElementsByTagName('head')->item(0);
            if ($head) {
                foreach ($head->getElementsByTagName('style') as $styleNode) {
                    $css .= $styleNode->nodeValue;
                }
            }

            $body = $dom->getElementsByTagName('body')->item(0);
            if ($body) {
                foreach ($body->childNodes as $child) {
                    $bodyHtml .= $dom->saveHTML($child);
                }
            } else {
                preg_match('/<body[^>]*>(.*?)<\/body>/s', $htmlContent, $matches);
                $bodyHtml = $matches[1] ?? $htmlContent;
            }

            // Inline CSS
            $inliner = new CssToInlineStyles();
            $inlinedHtml = $inliner->convert($bodyHtml, $css);

            return response()->json([
                'html' => $inlinedHtml
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        } finally {
            if (file_exists($docxPath)) unlink($docxPath);
            if (file_exists($htmlPath)) unlink($htmlPath);
        }
    }

    public function export(Request $request)
    {
        $html = $request->input('html');

        if (!$html) {
            return response()->json(['error' => 'Thiếu nội dung HTML'], 400);
        }

        $html = preg_replace('/on\w+\s*=\s*"[^"]*"/', '', $html); // Xóa JS nguy hiểm
        $uuid = Str::uuid();
        $inputHtml = storage_path("app/temp/$uuid.html");
        $outputDocx = storage_path("app/temp/$uuid.docx");

        try {
            $fullHtml = "<!DOCTYPE html><html><head><meta charset='utf-8'></head><body>$html</body></html>";
            file_put_contents($inputHtml, $fullHtml);

            $cmd = escapeshellarg($this->libreOfficeBinaryPath) .
                " --headless --convert-to docx " .
                escapeshellarg($inputHtml) .
                " --outdir " . escapeshellarg(storage_path('app/temp'));

            exec($cmd, $out, $code);

            if ($code !== 0 || !file_exists($outputDocx) || filesize($outputDocx) === 0) {
                return response()->json(['error' => 'Export thất bại'], 500);
            }

            return response()->download($outputDocx, 'xuat-file.docx')->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        } finally {
            if (file_exists($inputHtml)) unlink($inputHtml);
            if (file_exists($outputDocx)) unlink($outputDocx);
        }
    }
    public function getFile($filename)
    {
        $path = storage_path("app/public/{$filename}");
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        return response()->file($path);
    }

    public function handleSave(Request $request)
    {
        $downloadUri = $request->input('url');
        if ($downloadUri) {
            $contents = file_get_contents($downloadUri);
            file_put_contents(storage_path('app/documents/edited.docx'), $contents);
            return response()->json(['status' => 'success']);
        }
        return response()->json(['error' => 'Missing file URL'], 400);
    }
    // app/Http/Controllers/DocxController.php


    public function getDocxHtml($filename)
    {
        $path = storage_path("app/public/{$filename}");
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        try {
            $phpWord = IOFactory::load($path);
            $writer = new \PhpOffice\PhpWord\Writer\HTML($phpWord);
            ob_start();
            $writer->save('php://output');
            $html = ob_get_clean();

            return response()->json(['html' => $html]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function convertDocxStoredAndResend()
    {
        $apiToken = 'qs2x0kwunb2begoc4cmbvtf00br98ngvsq2wtkiykz4zlcyi';
        $filePath = storage_path('app/public/test.docx');

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Step 1: Convert DOCX → HTML
        $responseHtml = Http::withToken($apiToken)
            ->attach('file', file_get_contents($filePath), 'test.docx')
            ->post('https://api.tiny.cloud/api/docx/convert');

        if (!$responseHtml->successful()) {
            return response()->json(['error' => 'Docx to HTML failed', 'detail' => $responseHtml->body()], 500);
        }

        $html = $responseHtml->json('html');

        // Step 2: Convert HTML → DOCX
        $responseDocx = Http::withToken($apiToken)
            ->withHeaders([
                'Accept' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->post('https://api.tiny.cloud/api/html/convert', [
                'html' => $html,
                'css' => 'p { font-family: Arial; }', // optional
            ]);

        if (!$responseDocx->successful()) {
            return response()->json(['error' => 'HTML to Docx failed', 'detail' => $responseDocx->body()], 500);
        }

        return response($responseDocx->body(), 200)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->header('Content-Disposition', 'attachment; filename=converted.docx');
    }
    private function extractDocxPlaceholders($filePath)
    {
        if (!file_exists($filePath)) {
            return ['❌ File không tồn tại'];
        }

        $zip = new \ZipArchive;
        $res = $zip->open($filePath);

        if ($res !== true) {
            return ['❌ Không mở được file DOCX. Mã lỗi: ' . $res];
        }

        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$content) {
            return ['❌ Không đọc được nội dung word/document.xml'];
        }

        $xml = str_replace(['</w:t><w:t>', '</w:t>', '<w:t>'], '', $content);
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $xml, $matches);

        return array_unique($matches[1]);
    }
    public function convertDocxToHtml($filename)
    {
        $filePath = storage_path('app/public/documents/' . $filename);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File không tồn tại'], 404);
        }

        try {
            $phpWord = IOFactory::load($filePath, 'Word2007');
            $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            ob_start();
            $htmlWriter->save('php://output');
            $html = ob_get_clean();

            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Không đọc được file: ' . $e->getMessage()], 500);
        }
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

    private function getSofficePath(): string
    {
        $envPath = env('SOFFICE_PATH');
        if ($envPath && file_exists(str_replace('"', '', $envPath))) {
            return $envPath;
        }

        switch (PHP_OS_FAMILY) {
            case 'Windows':
                return '"C:\Program Files\LibreOffice\program\soffice.exe"';
            case 'Darwin': // macOS
                return '/Applications/LibreOffice.app/Contents/MacOS/soffice';
            case 'Linux':
            default:
                return 'libreoffice';
        }
    }
    public function convertDocxToHtml1(string $filename)
    {
        $inputPath = storage_path("app/public/documents/{$filename}");
        $outputDir = storage_path('app/public/html');

        // Kiểm tra file đầu vào
        if (!file_exists($inputPath)) {
            return response()->json(['message' => 'File không tồn tại'], 404);
        }

        // Tạo thư mục chứa file HTML nếu chưa có
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Đường dẫn đến LibreOffice
        $soffice = $this->getSofficePath();

        // Lệnh chuyển đổi DOCX → HTML
        $cmd = $soffice . ' --headless --convert-to html --outdir ' .
            escapeshellarg($outputDir) . ' ' . escapeshellarg($inputPath);

        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0) {
            return response()->json(['message' => 'Chuyển đổi thất bại'], 500);
        }

        $htmlFilename = pathinfo($filename, PATHINFO_FILENAME) . '.html';
        $htmlPath = $outputDir . '/' . $htmlFilename;

        if (!file_exists($htmlPath)) {
            return response()->json(['message' => 'Không tìm thấy file HTML sau khi chuyển đổi'], 500);
        }

        $htmlContent = file_get_contents($htmlPath);

        // ✅ Trả HTML để preview
        return response()->json([
            'html'=> $htmlContent,
        ]);
    }
}
