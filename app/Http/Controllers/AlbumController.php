<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AlbumController extends Controller
{
    public function show(int $id)
    {
        $cacheKey = 'album_' . $id;
        $cachedData = Cache::get($cacheKey);
        $albumData = $cachedData['data'] ?? null;
        $timestamp = $cachedData['timestamp'] ?? null;

        $currentTime = time();

        if ($albumData === null && $timestamp === null) {
            $albumData = $this->fetchAlbumData($id);
            
            if ($albumData) {
                $albumData['timesGenerated'] = 0;
                $cachedData = ['data' => $albumData, 'timestamp' => $currentTime];
                Cache::put($cacheKey, $cachedData, 60); // Cache the new album data for 60 seconds
            } else {
                // Handle error if API request fails
                $albumData = null;
            }
        } elseif ($albumData && ($currentTime - $timestamp) >= 60) {
            $newAlbumData = $this->fetchAlbumData($id);

            if ($newAlbumData && $this->hasAlbumDataChanged($albumData, $newAlbumData)) {
                $newAlbumData['timesGenerated'] = $albumData['timesGenerated'] + 1;
                $cachedData = ['data' => $newAlbumData, 'timestamp' => $currentTime];
                Cache::put($cacheKey, $cachedData, 60); // Cache the new album data for 60 seconds
                $albumData = $newAlbumData;
            }
        }

        return view('album', compact('albumData'));
    }

    private function fetchAlbumData(int $id)
    {
        $apiResponse = Http::get("https://itunes.apple.com/lookup?id=$id&entity=album");
        $songs = Http::get("https://itunes.apple.com/lookup?id=$id&entity=song");

        if ($apiResponse->ok() && $songs->ok() && $apiResponse->json()['resultCount'] > 0) {
            $apiData = $apiResponse->json();
            $songsData = $songs->json();
            $albumData = $apiData['results'][0];
            $albumData['songs'] = [];

            foreach ($songsData['results'] as $song) {
                if ($song['wrapperType'] === 'track') {
                    $albumData['songs'][] = $song;
                }
            }

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
            unset($albumData['songs']);

            $albumData['artworkUrl400'] = str_replace('100x100', '400x400', $albumData['artworkUrl100']);
            
            return $albumData;
        }

        return null;
    }

    private function hasAlbumDataChanged(array $oldData, array $newData)
    {
        $serializedOldData = serialize($oldData);
        $serializedNewData = serialize($newData);

        return $serializedOldData !== $serializedNewData;
    }
}
