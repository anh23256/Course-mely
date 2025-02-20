<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Documents\DocumentRequest;
use App\Models\Chapter;
use App\Models\Document;
use App\Models\Lesson;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    use LoggableTrait, UploadToLocalTrait, ApiResponseTrait;

    const DOCUMENT_LESSON = 'documents/lessons';

    public function index(string $id)
    {
        try {
            $documents = Document::query()
                ->whereHas('lessons', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->latest('id')->get();

            return response()->json([
                'message' => 'Danh sách tài liệu',
                'status' => true,
                'document' => $documents
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Không thể tải danh sách tài liệu, vui lòng thử lại');
        }
    }

    public function show(string $id)
    {
        try {
            $document = Document::findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết tài liệu',
                'status' => true,
                'document' => $document
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondNotFound('Tài liệu không tồn tại');
        }
    }

    public function storeLessonDocument(DocumentRequest $request, string $chapterId)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $data['slug'] = !empty($data['title'])
                ? Str::slug($data['title']) . '-' . Str::uuid()
                : Str::uuid();

            $chapter = Chapter::query()->where('id', $chapterId)->first();

            if (!$chapter) {
                return $this->respondNotFound('Không tìm thấy chương học');
            }

            if ($chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            if ($request->hasFile('document_file')) {
                $documentFile = $request->file('document_file');
                $data['file_path'] = $this->uploadToLocal($documentFile, self::DOCUMENT_LESSON);
                $data['file_type'] = 'upload';
            } elseif (!empty($request->document_url)) {
                $data['file_path'] = $request->document_url;
                $data['file_type'] = 'url';
            }

            $document = Document::query()->create($data);

            $data['order'] = $chapter->lessons->max('order') + 1;

            $lesson = Lesson::query()->create([
                'chapter_id' => $chapter->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'type' => 'document',
                'lessonable_type' => Document::class,
                'lessonable_id' => $document->id,
                'order' => $data['order'],
                'is_free_preview' => $data['is_free_preview'] ?? false,
            ]);

            DB::commit();

            return $this->respondCreated('Tạo tài liệu thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function update(DocumentRequest $request, string $id)
    {
        try {
            $document = Document::find($id);

            if (!$document) {
                return $this->respondNotFound('Tài liệu không tồn tại');
            }

            $data = $request->validated();

            if ($request->hasFile('document_file')) {
                $documentFile = $request->file('document_file');
                $data['file_path'] = $this->uploadToLocal($documentFile, self::DOCUMENT_LESSON);
                $data['file_type'] = 'upload';
            } elseif (!empty($request->document_url)) {
                $data['file_path'] = $request->document_url;
                $data['file_type'] = 'url';
            }

            $data['title'] = !empty($data['title']) ? $data['title'] : Str::uuid();

            $document = $document->update($data);

            return response()->json([
                'message' => 'Tài liệu đã được cập nhật thành công',
                'status' => true,
                'document' => $document
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function destroy(string $id)
    {
        try {
            $document = Document::find($id);

            if (!$document) {
                return $this->respondNotFound('Tài liệu không tồn tại');
            }

            if ($document->file_type === 'upload' && Storage::exists($document->file_path)) {
                $this->deleteFromLocal($document->file_path, self::DOCUMENT_LESSON);
            }

            $document->delete();

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
