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
            $timesGenerated = 0;
            if ($apiResponse->ok() && $songs->ok() && $apiResponse['resultCount'] > 0) {
                $apiData = $apiResponse->json();
                $songsData = $songs->json();
                $albumData = $apiData['results'][0];
                foreach ($songsData['results'] as $song) {
                    if ($song['wrapperType'] === 'track') {
                        $albumData['songs'][] = $song;
                    }
                }
                // if the album has multiple discs, we need to group the songs by disc number, then each disc by track number
                $discs = [];
                foreach ($albumData['songs'] as $song) {
                    $discs[$song['discNumber']][] = $song;
                }
                $albumData['discs'] = $discs;
                foreach ($albumData['discs'] as $discNumber => $disc) {
                    usort($albumData['discs'][$discNumber], function ($a, $b) {
                        return $a['trackNumber'] <=> $b['trackNumber'];
                    });
                }
                $albumData['previewUrl'] = $albumData['discs'][1][0]['previewUrl'];
                // remove songs from the album data since we don't need them anymore
                unset($albumData['songs']);


                $albumData['artworkUrl400'] = str_replace('100x100', '400x400', $albumData['artworkUrl100']);
                $albumData['timesGenerated'] = $timesGenerated;
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

                    // if the album has multiple discs, we need to group the songs by disc number, then each disc by track number
                    $discs = [];
                    foreach ($NewalbumData['songs'] as $song) {
                        $discs[$song['discNumber']][] = $song;
                    }
                    $NewalbumData['discs'] = $discs;
                    foreach ($NewalbumData['discs'] as $discNumber => $disc) {
                        usort($NewalbumData['discs'][$discNumber], function ($a, $b) {
                            return $a['trackNumber'] <=> $b['trackNumber'];
                        });
                    }
                    $NewalbumData['previewUrl'] = $NewalbumData['discs'][1][0]['previewUrl'];
                    // remove songs from the album data since we don't need them anymore
                    unset($NewalbumData['songs']);


                    $NewalbumData['artworkUrl400'] = str_replace('100x100', '400x400', $NewalbumData['artworkUrl100']);
                    $NewalbumData['timesGenerated'] = $cachedData['data']['timesGenerated'] + 1;

                    $oldData = serialize($albumData);
                    $NewData = serialize($NewalbumData);


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
