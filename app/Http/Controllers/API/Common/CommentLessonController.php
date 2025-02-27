<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Lessons\ReplyCommentLessonRequest;
use App\Http\Requests\API\Lessons\StoreCommentLessonRequest;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Lesson;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentLessonController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getCommentLesson(Request $request, $lessonId)
    {
        try {
            $commentLessons = Comment::query()
                ->with('user')
                ->where('commentable_type', Lesson::class)
                ->where('commentable_id', $lessonId)
                ->where('parent_id', null)
                ->get();

            if (!$commentLessons) {
                return $this->respondNotFound('Không tìm thấy bình luận');
            }

            return $this->respondSuccess('Danh sách bình luận', $commentLessons);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function storeCommentLesson(StoreCommentLessonRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $data = $request->validated();

            $lessons = Lesson::query()->find($data['lesson_id']);

            if (!$lessons) {
                return $this->respondNotFound('Không tìm thấy lớp học');
            }

            $comment = Comment::query()->create([
                'user_id' => $user->id,
                'content' => $data['content'] ?? '',
                'commentable_id' => $lessons->id,
                'commentable_type' => Lesson::class,
                'parent_id' => null,
            ]);

            return $this->respondCreated('Bình luận thành công', $comment);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function getReplies(Request $request, string $commentId)
    {
        try {
            $parentComment = Comment::query()
                ->find($commentId);

            if (!$parentComment) {
                return $this->respondNotFound('Không có bình luận cha');
            }

            $offset = $request->get('offset', 0);
            $limit = $request->get('limit', 3);

            $replies = $parentComment->replies()
                ->latest()
                ->offset($offset)
                ->limit($limit)
                ->get();
            if (!$replies) {
                return $this->respondNotFound('Không có bình luận cha');
            }

            return $this->respondOk('Danh sách phản hồi', $replies);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function reply(ReplyCommentLessonRequest $request, $commentId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $data = $request->validated();

            $parentComment = Comment::query()->find($commentId);

            if (!$parentComment) {
                return $this->respondNotFound('Không có bình luận cha');
            }

            $reply = Comment::query()->create([
                'user_id' => $user->id,
                'content' => $data['content'] ?? '',
                'parent_id' => $parentComment->id,
                'commentable_id' => $parentComment->commentable_id,
                'commentable_type' => Lesson::class,
            ]);

            return $this->respondCreated('Phản hồi bình luận thành công', $reply);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }
}
