<?php

namespace App\Traits;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait UploadToCloudinaryTrait
{
    use LoggableTrait;

    public function uploadImage($file, $folder = null)
    {
        try {
            if (!$file->isValid()) {
                return null;
            }

            $uploadResult = Cloudinary::upload($file->getRealPath(), [
                'folder' => $folder ?? 'images',
                'public_id' => Str::random(10),
            ]);

            return $uploadResult->getSecurePath() ?? null;

        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadImageMultiple($files, $folder = null)
    {
        try {
            $uploadedUrls = [];

            foreach ($files as $file) {
                if (!$file->isValid()) {
                    continue;
                }

                $uploadResult = Cloudinary::upload($file->getRealPath(), [
                    'folder' => $folder ?? 'images',
                    'public_id' => Str::random(10),
                ]);

                $uploadedUrls[] = $uploadResult->getSecurePath() ?? null;
            }

            return $uploadedUrls;

        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadVideo($file, $folder = null, $fullInfo = false)
    {
        try {
            if (!$file->isValid()) {
                return null;
            }

            $fileSize = $file->getSize();

            $uploadOptions = [
                'folder' => $folder ?? 'videos',
                'public_id' => Str::random(10),
                'resource_type' => 'video',
                'eager_async' => true,
//                'timeout' => 600,
            ];

            if ($fileSize > 10 * 1048576) {
                $uploadOptions['chunk_size'] = 10 * 1048576;
            }

            $uploadResult = Cloudinary::uploadVideo($file->getRealPath(), $uploadOptions);

            $secure_url = $uploadResult->getSecurePath() ?? null;
            if (!$secure_url) {
                throw new \Exception('Upload video thất bại');
            }

            if ($fullInfo) {
                $publicId = $uploadResult->getPublicId() ?? null;
                return [
                    'secure_url' => $secure_url,
                    'public_id' => $publicId,
                ];
            }

            return $secure_url;
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteImage($dataUrl, $folder = null)
    {
        try {
            if (empty($dataUrl) || !filter_var($dataUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('URL không hợp lệ hoặc không được cung cấp.');
            }

            $publicId = pathinfo(parse_url($dataUrl, PHP_URL_PATH), PATHINFO_FILENAME);

            $publicIdWithFolder = ($folder ?? 'images') . '/' . $publicId;

            $deleteResult = Cloudinary::destroy($publicIdWithFolder);

            if ($deleteResult['result'] !== 'ok') {
                throw new \Exception('Không thể xóa ảnh');
            }

            return true;
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteMultiple(array $dataUrls, $folder = null)
    {
        try {
            if (empty($dataUrls)) {
                throw new \Exception('Danh sách URL không hợp lệ hoặc không được cung cấp.');
            }

            $deleteResults = [];

            foreach ($dataUrls as $dataUrl) {
                if (!filter_var($dataUrl, FILTER_VALIDATE_URL)) {
                    throw new \Exception("URL không hợp lệ: $dataUrl");
                }

                $publicId = pathinfo(parse_url($dataUrl, PHP_URL_PATH), PATHINFO_FILENAME);
                $publicIdWithFolder = ($folder ?? 'images') . '/' . $publicId;

                $deleteResult = Cloudinary::destroy($publicIdWithFolder);

                if ($deleteResult['result'] !== 'ok') {
                    $deleteResults[] = [
                        'url' => $dataUrl,
                        'status' => 'failed',
                    ];
                } else {
                    $deleteResults[] = [
                        'url' => $dataUrl,
                        'status' => 'ok',
                    ];
                }
            }

            if (empty($deleteResults)) {
                throw new \Exception('Không thể xóa ảnh');
            }

            return $deleteResults;
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function deleteVideo($dataUrl, $folder = null)
    {
        try {
            if (empty($dataUrl) || !filter_var($dataUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('URL không hợp lệ hoặc không được cung cấp.');
            }
            $publicId = pathinfo(parse_url($dataUrl, PHP_URL_PATH), PATHINFO_FILENAME);

            $publicIdWithFolder = ($folder ?? 'images') . '/' . $publicId;

            $deleteResult = Cloudinary::destroy($publicIdWithFolder, [
                'resource_type' => 'video',
            ]);

            if ($deleteResult['result'] !== 'ok') {
                throw new \Exception('Không thể xóa video');
            }

            return true;
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
