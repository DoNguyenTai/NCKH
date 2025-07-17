<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeController extends Controller
{
    public function scrapeElement(Request $request)
    {
        $url = $request->query('url');
        $selector = $request->query('selector');

        if (!$url || !$selector) {
            return response()->json([
                'error' => 'Thiếu tham số "url" hoặc "selector".'
            ], 400);
        }

        try {
            $client = new Client([
                'verify' => false,
            ]);
            $response = $client->get($url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $texts = $crawler->filter($selector)->each(function ($node) {
                return trim($node->text());
            });
            Log::info($texts);

            if ($texts) {
                return response()->json([
                    'success' => true,
                    'data' => $texts,
                    'sourceUrl' => $url,
                    'usedSelector' => $selector
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy phần tử nào với selector: \"$selector\" trên trang: $url"
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Lỗi khi lấy dữ liệu từ trang.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
