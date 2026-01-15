<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TravelController extends Controller
{
    public function getPlan(Request $request)
    {
        $request->validate([
            'location' => 'required|string',
            'month'    => 'required|string',
            'budget'   => 'required|numeric',
            'days'     => 'required|numeric',
        ]);

        $prompt = "তুমি একজন ট্রাভেল গাইড। {$request->month} মাসে {$request->days} দিনের জন্য {$request->location} ভ্রমণের প্ল্যান দাও। বাজেট {$request->budget} টাকা। উত্তর বাংলায় এবং HTML format এ দাও (div, h3, ul, li ট্যাগ ব্যবহার করে)।";

        $apiKey = env('GEMINI_API_KEY');

        // পরিবর্তন: আপনার লিস্টে থাকা 'gemini-flash-latest' ব্যবহার করা হলো।
        // এটি অটোমেটিক লেটেস্ট স্টেবল ভার্সন (যেমন 2.5 বা 1.5) সিলেক্ট করবে যা আপনার কি-তে সাপোর্ট করে।
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}";

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $aiText = $data['candidates'][0]['content']['parts'][0]['text'];
                    $cleanText = str_replace(['```html', '```'], '', $aiText);
                    return response()->json(['plan' => $cleanText]);
                }
            }

            return response()->json([
                'plan' => 'Google Error: ' . $response->status() . ' - ' . $response->body()
            ], 500);

        } catch (\Exception $e) {
            return response()->json(['plan' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}