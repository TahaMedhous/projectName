<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AlbumController extends Controller
{
    public function show($id)
    {
        $cacheKey = 'album_' . $id;
        $cachedData = Cache::get($cacheKey);
        $albumData = $cachedData['data'] ?? null;
        $timestamp = $cachedData['timestamp'] ?? null;

        $currentTime = time();
        // if the album is null, then the page was never generated before
        if ($albumData === null && $timestamp === null) {
            $apiResponse = Http::get("https://itunes.apple.com/lookup?id=$id&entity=album");
            $songs = Http::get("https://itunes.apple.com/lookup?id=$id&entity=song");
            if ($apiResponse->ok() && $songs->ok() && $apiResponse['resultCount'] > 0) {
                $apiData = $apiResponse->json();
                $songsData = $songs->json();
                $albumData = $apiData['results'][0];
                foreach ($songsData['results'] as $song) {
                    if ($song['wrapperType'] === 'track') {
                        $albumData['songs'][] = $song;
                    }
                }
                usort($albumData['songs'], function ($a, $b) {
                    return $a['trackNumber'] <=> $b['trackNumber'];
                });

                $albumData['previewUrl'] = $albumData['songs'][0]['previewUrl'];
                $albumData['artworkUrl1000'] = str_replace('100x100', '1000x1000', $albumData['artworkUrl100']);
                // Update the cached album data and timestamp
                $cachedData = ['data' => $albumData, 'timestamp' => $currentTime];
                Cache::put($cacheKey, $cachedData, 60); // Cache the new album data for 60 seconds
            } else {
                // Handle error if API request fails
                $albumData = null;
            }
        } else {
            // Check if it has been 60 seconds since the last update
            if ($albumData && ($currentTime - $timestamp) < 60) {
                // Return the cached album data if it has been less than 60 seconds
                return view('album', compact('albumData'));
            } else {
                $apiResponse = Http::get("https://itunes.apple.com/lookup?id=$id&entity=album");
                $songs = Http::get("https://itunes.apple.com/lookup?id=$id&entity=song");

                if ($apiResponse->ok() && $songs->ok() && $apiResponse->json()['resultCount'] > 0) {
                    $apiData = $apiResponse->json();
                    $songsData = $songs->json();
                    $NewalbumData = $apiData['results'][0];
                    foreach ($songsData['results'] as $song) {
                        if ($song['wrapperType'] === 'track') {
                            $NewalbumData['songs'][] = $song;
                        }
                    }
                    usort($NewalbumData['songs'], function ($a, $b) {
                        return $a['trackNumber'] <=> $b['trackNumber'];
                    });
                    $NewalbumData['previewUrl'] = $NewalbumData['songs'][0]['previewUrl'];
                    $NewalbumData['artworkUrl1000'] = str_replace('100x100', '1000x1000', $NewalbumData['artworkUrl100']);
                    // check if the album data has changed by comparing the old and new data
                    // to be precise, we are going to turn both arrays into strings and compare
                    // after removing the update key cause it will always be different
                    $oldData = implode($albumData);
                    $NewData = implode($NewalbumData);

                    if ($NewData !== $oldData) {
                        $cachedData = ['data' => $NewalbumData, 'timestamp' => $currentTime];
                        Cache::put($cacheKey, $cachedData, 60); // Cache the new album data for 60 seconds
                    } else {
                        // Return the cached album data if it has been less than 60 seconds
                        return view('album', compact('albumData'));
                    }
                } else {
                    // Handle error if API request fails
                    $albumData = null;
                }
            }
        }
        return view('album', compact('albumData'));
    }
}
