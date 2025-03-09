<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Traits\FilterTrait;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use FilterTrait;
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
