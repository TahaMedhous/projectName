<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class StaticPageController extends Controller
{
    public function index()
    {
        $cachedData = Cache::get('page_data');
        $data = $cachedData['data'] ?? null;
        $decrypted_data = $cachedData['decrypted_data'] ?? null;
        $timestamp = $cachedData['timestamp'] ?? null;

        $currentTime = time();

        // Check if the timestamp exists and if it has been 60 seconds since the last update
        if ($timestamp && ($currentTime - $timestamp) < 60) {
            // Return the cached data
            return view('static_page', compact('data', 'decrypted_data'));
        }

        $apiResponse = Http::get('https://api.breakingbadquotes.xyz/v1/quotes');

        if ($apiResponse->ok()) {
            $apiData = $apiResponse->json();
            $data = $apiData[0]['quote'];

            // Update the cached data and timestamp
            $key = 'hello world';
            $encryptedData = encrypt($data, $key);
            $cachedData = ['data' => $encryptedData, 'decrypted_data' => $data, 'timestamp' => $currentTime];
            Cache::put('page_data', $cachedData, 60); // Cache the new data for 60 seconds
        } else {
            // Handle error if API request fails
            $data = null;
        }

        return view('static_page', compact('data', 'decrypted_data'));
    }
}
