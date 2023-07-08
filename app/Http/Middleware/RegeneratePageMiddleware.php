<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RegeneratePageMiddleware
{
    public function handle($request, Closure $next)
    {
        $pageData = Cache::get('page_data');
        $timestamp = $pageData['timestamp'] ?? null;

        $currentTime = time();

        // Check if the timestamp exists and if it has been 60 seconds since the last update
        if (!$timestamp || ($currentTime - $timestamp) >= 60) {
            // Regenerate the page
            $apiResponse = Http::get('https://api.breakingbadquotes.xyz/v1/quotes');

            if ($apiResponse->ok()) {
                $apiData = $apiResponse->json();
                $data = $apiData[0]['quote'];
                $encryptedData = encrypt($data);
                // well encrypt the data so that its harder to scraped/parsed as json,
                // and only available as readable text in html
                // at least well give them a hard time scraping the data through html parsing

                // Update the cached data and timestamp
                $pageData = ['data' => $encryptedData, 'decrypted_data' => $data, 'timestamp' => $currentTime];
                Cache::put('page_data', $pageData, 60); // Cache the new data for 60 seconds
            }
        }

        return $next($request);
    }
}
