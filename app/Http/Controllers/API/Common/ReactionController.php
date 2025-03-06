<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Reactions\StoreReactionRequest;
use App\Models\Comment;
use App\Models\Reaction;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    use LoggableTrait, ApiResponseTrait;
    public function toggleReaction(StoreReactionRequest $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }
            $data = $request->validated();
            $comment = Comment::find($data['comment_id']);
            if (!$comment) {
                return $this->respondNotFound('Không tìm thấy comment');
            }
            $reaction = Reaction::where([
                'user_id' => $user->id,
                'reactable_id' => $comment->id,
                'reactable_type' => Comment::class,
            ])->first();
            if ($reaction) {

                if ($reaction->type == $data['type']) {
                    // Nếu phản ứng giống với trước, xóa phản ứng
                    $reaction->delete();
                    return response()->json(['message' => 'Xóa thành công']);
                } else {
                    // Nếu phản ứng khác với trước, cập nhật loại phản ứng
                    $reaction->update([
                        'type' => $data['type'],  
                    ]);
                    return response()->json(['message' => 'Cập nhật thành công', 'reaction' => $reaction]);
                }
            }
            $newReaction = Reaction::create(
                [
                    'user_id' => $user->id,
                    'reactable_id' => $comment->id,
                    'reactable_type' => Comment::class,
                    'type' => $request->type,
                ],
            );
    
            return $this->respondSuccess('Thả reaction thành công');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }
    public function index($commentId)
{
    try {
        // Tìm comment theo ID
        $comment = Comment::find($commentId);
        if (!$comment) {
            return $this->respondNotFound('Không tìm thấy comment');
        }

        // Lấy tất cả các phản ứng của comment đó
        $reactions = Reaction::where([
            'reactable_id' => $comment->id,
            'reactable_type' => Comment::class,
        ])->with('user')->get();
        $likeCount = $reactions->where('type', 'like')->count();
        $loveCount = $reactions->where('type', 'love')->count();
        $hahaCount = $reactions->where('type', 'haha')->count();
        $wowCount = $reactions->where('type', 'wow')->count();
        $sadCount = $reactions->where('type', 'sad')->count();
        $angryCount = $reactions->where('type', 'angry')->count();

        $totalReactions =  $likeCount +  $loveCount +  $hahaCount + $wowCount + $sadCount + $angryCount;
        // Trả về danh sách các phản ứng
        return response()->json([
            'message' => 'Lấy phản ứng thành công',
            'reactions' => $reactions->map(function($reaction) {
                return [
                    'user_name' => $reaction->user->name, 
                    'avatar' => $reaction->user->avatar, 
                    'react_type' => $reaction->type,
                ];
            }),
            'totalReactions' => $totalReactions,
            'like_count' => $likeCount,
            'love_count' => $loveCount,
            'haha_count' => $hahaCount,
            'sad_count' => $sadCount,
            'wow_count' => $wowCount,
            'angry_count' => $angryCount,
        ]);
    } catch (\Exception $e) {
        // Ghi log lỗi nếu có
        $this->logError($e, ['comment_id' => $commentId]);

        return $this->respondServerError();
    }
}
}
