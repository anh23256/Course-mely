<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Services\VideoUploadService;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToMuxTrait;
use Illuminate\Http\Request;

class HandleVideoController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToCloudinaryTrait;

    protected $videoUploadService;

    public function __construct(VideoUploadService $videoUploadService)
    {
        $this->videoUploadService = $videoUploadService;
    }

    public function handleUpload(Request $request)
    {
        try {
            $data = $request->validate([
                'video_file' => 'required|mimes:mp4,avi,mkv,flv|file',
            ]);

            if ($request->hasFile('video_file')) {
                $videoUrl = $this->uploadVideo($data['video_file'], true);
                $assetId = $this->videoUploadService->uploadVideoToMux($videoUrl);

                return $this->respondSuccess('Upload video thành công', [
                    'video_url' => $videoUrl,
                    'asset_id' => $assetId,
                    'duration' => $videoUrl['duration'],
                ]);
            } else {
                return $this->respondBadRequest('Dữ liệu không hợp lệ');
            }
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

}
