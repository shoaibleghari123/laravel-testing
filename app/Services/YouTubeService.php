<?php
namespace app\Services;

use Illuminate\Support\Facades\Http;

class YouTubeService
{
    public function getThumbnailByID($youtubeId)
    {
        $response = Http::asJson()
            ->baseUrl("https://youtube.googleapis.com/youtube/v3/")
            //->baseUrl("https://www.youtube.com/watch?v=" . $youtubeId)
            ->get('videos', [
                'part' => 'snippet',
                'id' => $youtubeId,
                'key' => env('YOUTUBE_API_KEY'),
            ])->collect('items');
        return $response[0]['snippet']['thumbnails']['default']['url'];
    }
}
