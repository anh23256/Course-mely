<?php

namespace App\Services;

use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use MuxPhp;

class VideoUploadService
{
    use UploadToCloudinaryTrait, LoggableTrait, ApiResponseTrait;

    protected $muxTokenId;
    protected $muxTokenSecret;

    const MUX_API_URL = 'https://api.mux.com/video/v1/assets';

    public function __construct()
    {
        $this->muxTokenId = config('services.mux.token_id');
        $this->muxTokenSecret = config('services.mux.token_secret');
    }

    public function uploadVideoToMux($videoUrl)
    {
        try {
            $httpClient = new Client();

            $response = $httpClient->request('POST', self::MUX_API_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->muxTokenId . ':' . $this->muxTokenSecret),
                ],
                'json' => [
                    'input' => $videoUrl,
                    'playback_policy' => [
                        MuxPhp\Models\PlaybackPolicy::_PUBLIC
                    ]
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            $assetId = $responseData['data']['id'] ?? null;
            $playbackId = $responseData['data']['playback_ids'][0]['id'] ?? null;

            return [
                'asset_id' => $assetId,
                'playback_id' => $playbackId
            ];
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra khi upload video, vui lòng thử lại');
        }
    }

    public function getVideoDurationToMux($assetId)
    {
        try {
            $httpClient = new Client();

            $response = $httpClient->request('GET', self::MUX_API_URL . '/' . $assetId, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->muxTokenId . ':' . $this->muxTokenSecret),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $duration = $data['data']['duration'] ?? null;

            return $duration;
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra khi lấy thời lượng video, vui lòng thử lại');
        }
    }

    public function deleteVideoFromMux($assetId)
    {
        try {
            $httpClient = new Client();

            $response = $httpClient->request("DELETE", self::MUX_API_URL . '/' . $assetId, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->muxTokenId . ':' . $this->muxTokenSecret),
                ],
            ]);

            if ($response->getStatusCode() !== 204) {
                throw new \Exception('Không thể xóa video');
            }

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra khi xóa video, vui lòng thử lại');
        }
    }
}
