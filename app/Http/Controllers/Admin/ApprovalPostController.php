<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approvable;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostApprovalNotification;
use App\Notifications\PostSubmittedForApprovalNotification;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalPostController extends Controller
{
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        $title = 'Kiểm duyệt bài viết';
        $subTitle = 'Danh sách bài viết';

        $queryApprovals = Approvable::query()
            ->with([
                'approver',
                'post.user',
            ])
            ->orderBy('id', 'desc')
            ->where('approvable_type', Post::class);

        $approvalCount = Approvable::query()
            ->selectRaw('
                count(id) as total_approval,
                sum(status = "pending") as pending_approval,
                sum(status = "approved") as approved_approval,
                sum(status = "rejected") as rejected_approval
            ')
            ->where('approvable_type', Post::class)
            ->first();

        if ($request->hasAny([
            'request_start_date',
            'request_end_date',
            'approval_start_date',
            'approval_end_date',
            'post_title_approved',
            'approver_name_approved',
            'name_creator',
            'phone_creator',
            'creator_email',
            'status'
        ])) {
            $queryApprovals = $this->filter($request, $queryApprovals);
        }

        if ($request->has('search_full')) {
            $queryApprovals = $this->search($request, $queryApprovals);
        }

        $approvals = $queryApprovals->paginate(10);

        if ($request->ajax()) {
            $html = view('approval.post.table', compact('approvals'))->render();
            return response()->json(['html' => $html]);
        }

        return view('approval.post.index', compact([
            'title',
            'subTitle',
            'approvals',
            'approvalCount'
        ]));
    }

    public function show(string $id)
    {
        try {
            $approval = Approvable::query()
                ->where('approvable_type', Post::class)
                ->with([
                    'approver',
                    'approvable.user', // Tải quan hệ user của Post
                ])
                ->latest('created_at')
                ->findOrFail($id);

            $title = 'Kiểm duyệt bài viết';
            $subTitle = 'Thông tin bài viết: ' . $approval->approvable->title;

            return view('approval.post.show', compact([
                'title',
                'subTitle',
                'approval',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);
            return redirect()->route('admin.approvals.posts.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function approve(Request $request, string $id)
    {
        return $this->updateApprovalStatus($id, 'approved', 'Bài viết đã được duyệt');
    }

    public function reject(Request $request, string $id)
    {
        $note = $request->note ?? 'Bài viết đã bị từ chối';
        return $this->updateApprovalStatus($id, 'rejected', $note);
    }

    private function updateApprovalStatus(string $id, string $status, string $note)
    {
        try {
            DB::beginTransaction();

            $approval = Approvable::query()->findOrFail($id);

            $approval->status = $status;
            $approval->note = $note;
            $approval->{$status . '_at'} = now();
            $approval->approver_id = auth()->id();
            $approval->save();

            if ($status === 'approved') {
                // Khi duyệt, chuyển trạng thái thành published và cập nhật published_at
                $approval->approvable->update([
                    'status' => 'published', // Chuyển thành published
                    'published_at' => now(), // Đặt thời gian xuất bản là thời điểm duyệt
                ]);
            } else {
                // Khi từ chối, chuyển trạng thái sang draft để giảng viên chỉnh sửa
                $approval->approvable->update([
                    'status' => 'draft',
                ]);
            }
            // Gửi thông báo đến giảng viên
            $user = $approval->approvable->user;
            $user->notify(new PostApprovalNotification($approval->approvable, $status, $note));
            DB::commit();

            return redirect()->back()->with('success', "Bài viết đã được $status");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e);
            return redirect()->route('admin.approvals.posts.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function filter(Request $request, $query)
    {
        $filters = [
            'status' => ['queryWhere' => '='],
            'post_title_approved' => null,
            'approver_name_approved' => null,
            'request_date' => ['attribute' => ['request_start_date' => '>=', 'request_end_date' => '<=']],
            'approval_date' => ['filed' => ['approved_at', 'rejected_at'], 'attribute' => ['approval_start_date' => '>=', 'approval_end_date' => '<=']],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        $name_creator = $request->input('name_creator', '');
        $phone_creator = $request->input('phone_creator', '');
        $creator_email = $request->input('creator_email', '');
        if (!empty($name_creator) || !empty($phone_creator) || !empty($creator_email)) {
            $query->whereHas('post.user', function ($query) use ($name_creator, $phone_creator, $creator_email) {

                if (!empty($name_creator)) {
                    $query->where('name', 'LIKE', "%{$name_creator}%");
                }

                if (!empty($creator_email)) {
                    $query->where('email', 'LIKE', "%{$creator_email}%");
                }

                if (!empty($phone_creator)) {
                    $query->whereHas('profile', function ($query) use ($phone_creator) {
                        $query->where('phone', 'LIKE', "%$phone_creator%");
                    });
                }
            });
        }

        return $query;
    }

    private function search($request, $query)
    {
        if (!empty($request->search_full)) {
            $searchTerm = $request->search_full;
            $query->where(function ($query) use ($searchTerm) {
                $query->whereHas('approver', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%$searchTerm%");
                })
                    ->orWhereHas('post', function ($query) use ($searchTerm) {
                        $query->where('title', 'LIKE', "%$searchTerm%");
                    })
                    ->orWhereHas('post.user', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%")
                            ->orWhere('email', 'LIKE', "%$searchTerm%")
                            ->orWhereHas('profile', function ($query) use ($searchTerm) {
                                $query->where('phone', 'LIKE', "%$searchTerm%");
                            });
                    });
            });
        }

        return $query;
    }
}
