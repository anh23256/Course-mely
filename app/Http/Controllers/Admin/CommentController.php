<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    use FilterTrait, LoggableTrait;
    public function index(Request $request)
    {

        $title = 'Quản lý bình luận';
        $subTitle = 'Danh sách bình luận trên hệ thống';

        $queryComments = Comment::query()->with(['user'])
            ->select('comments.*')->withCount(['reactions as total_reactions']);;

        if ($request->has('query') && $request->input('query')) {
            $search = $request->input(key: 'query');
            $queryComments->where('content', 'like', "%$search%");
        }

        $queryComments = $this->filter($request, $queryComments);

        $comments = $queryComments->orderBy('created_at', 'desc')->paginate(10);

        if ($request->ajax()) {

            $html = view('comments.table', compact('comments'))->render();
            return response()->json(['html' => $html]);
        }

        return view('comments.index', compact('title', 'subTitle', 'comments'));
    }

    public function getReplies(Comment $comment)
    {
        $replies = $comment->replies()->with('user')->withCount('reactions')->latest()->get()->map(function ($reply) {
            return [
                'id' => $reply->id,
                'user_name' => $reply->user->name,
                'content' => $reply->content,
                'created_at' => $reply->created_at->format('d/m/Y H:i:s'),
                'reaction_count' => $reply->reactions_count,
            ];
        });

        return response()->json($replies);
    }

    private function deleteComments(array $commentIDs)
    {
        // Chỉ lấy các comment chưa bị xóa mềm
        $comments = Comment::query()->whereIn('id', $commentIDs)->get();

        foreach ($comments as $comment) {
            // Nếu là comment cha, xóa luôn các replies
            if ($comment->parent_id === null) {
                $comment->replies()->delete();
            }
            // Thực hiện soft delete
            $comment->delete();
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {
                // Nếu có dấu phẩy, nghĩa là xóa nhiều comment
                $commentIDs = explode(',', $id);
                $this->deleteComments($commentIDs);
            } else {
                // Nếu chỉ xóa 1 comment
                $comment = Comment::query()->findOrFail($id);
                $comment->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Xóa thất bại'
            ]);
        }
    }

    private function filter($request, $query)
    {
        $filters = [
            'user_name_comment' => null,
            'start_date' => ['queryWhere' => '>='],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
}
