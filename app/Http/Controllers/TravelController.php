<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TravelController extends Controller
{
    /**
     * Generate a travel plan using Google Gemini AI.
     */
    public function getPlan(Request $request)
    {
        // 1. Validation
        $request->validate([
            'location' => 'required|string',
            'month'    => 'required|string',
            'budget'   => 'required|numeric',
            'days'     => 'required|numeric',
        ]);

        // 2. Construct the English Prompt
        $prompt = "You are a professional travel guide. Create a travel itinerary for {$request->location} in the month of {$request->month} for {$request->days} days. The total budget is {$request->budget}. Provide the response in English and in HTML format using only <div>, <h3>, <ul>, and <li> tags.";

        $apiKey = env('GEMINI_API_KEY');

        // 3. API URL (Using v1beta for gemini-1.5-flash)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        try {
            // 4. API Request
            $response = Http::withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            // 5. Handling the Response
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $aiText = $data['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Remove Markdown code blocks if the AI includes them
                    $cleanText = str_replace(['```html', '```'], '', $aiText);
                    
                    return response()->json(['plan' => trim($cleanText)]);
                }
            }

            // Error from Google API
            return response()->json([
                'plan' => 'Google API Error: ' . $response->status() . ' - ' . $response->reason()
            ], 500);

        } catch (\Exception $e) {
            // General Server/Connection Error
            return response()->json(['plan' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
}
