<?php

namespace App\Services;

use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use MuxPhp;
use MuxPhp\Api\DirectUploadsApi;
use MuxPhp\Models\CreateDirectUploadRequest;

class VideoUploadService
{
    use UploadToCloudinaryTrait, LoggableTrait, ApiResponseTrait;

    protected $muxTokenId;
    protected $muxTokenSecret;

    const MUX_API_URL = 'https://api.mux.com/video/v1/assets';
    const MUX_API_URL_UPLOAD = 'https://api.mux.com/video/v1/uploads';

    public function __construct()
    {
        $this->muxTokenId = config('services.mux.token_id');
        $this->muxTokenSecret = config('services.mux.token_secret');
    }

    public function createUploadUrl()
    {
        try {
            $httpClient = new Client();

            $response = $httpClient->request('POST', self::MUX_API_URL_UPLOAD, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->muxTokenId . ':' . $this->muxTokenSecret),
                ],
                'json' => [
                    'new_asset_settings' => [
                        'playback_policy' => ['public'],
                    ],
                    'cors_origin' => '*'
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            $uploadUrl = $responseData['data']['url'] ?? null;

            if (!$uploadUrl) {
                throw new \Exception('Không thể lấy URL upload từ Mux.');
            }

            return [
                'upload_url' => $uploadUrl,
                'asset_id' => $responseData['data']['id'],
            ];
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Không thể tạo URL upload, vui lòng thử lại sau.');
        }
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

    public function getVideoInfoFromMux($uploadId)
    {
        try {
            $httpClient = new Client();

            // Gửi request GET để lấy thông tin video từ Mux bằng uploadId
            $response = $httpClient->request('GET', self::MUX_API_URL_UPLOAD . '/' . $uploadId, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->muxTokenId . ':' . $this->muxTokenSecret),
                ],
            ]);

            // Đọc nội dung phản hồi
            $data = json_decode($response->getBody()->getContents(), true);

            // Lấy asset_id từ phản hồi
            $assetId = $data['data']['asset_id'] ?? null;

            if (!$assetId) {
                throw new \Exception('Không tìm thấy asset_id trong phản hồi từ Mux');
            }

            // Sau khi có asset_id, lấy thêm thông tin chi tiết về video (playback_id và duration)
            $videoInfo = $this->getVideoDetails($assetId);

            return $videoInfo;
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra khi lấy thông tin video từ Mux, vui lòng thử lại');
        }
    }
    public function getVideoDetails($assetId)
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

            $playbackId = $data['data']['playback_ids'][0]['id'] ?? null;
            $duration = $data['data']['duration'] ?? null;

            return [
                'asset_id' => $assetId,
                'playback_id' => $playbackId,
                'duration' => $duration
            ];
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra khi lấy thông tin video chi tiết từ Mux, vui lòng thử lại');
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
