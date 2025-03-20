<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MembershipUserController extends Controller
{
    //
    public function index(Request $request)
    {
        $title = 'Quản lý thành viên';
        $subTitle = 'Danh sách thành viên';

        $queryMemberships = User::with([
            'invoices' => function ($query) {
                $query->where('invoice_type', 'membership')
                    ->selectRaw('user_id, course_id, COUNT(id) as total_registrations, SUM(amount) as total_spent, MAX(created_at) as latest_membership')
                    ->groupBy('user_id', 'course_id');
            }
        ])
            ->whereHas('invoices', fn($query) => $query->where('invoice_type', 'membership'));

        // Xử lý tìm kiếm
        if ($request->has('search_full') && $request->input('search_full')) {
            $search = $request->input('search_full');
            $queryMemberships->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $memberships = $queryMemberships->orderBy('created_at', 'desc')->paginate(10);

        if ($request->ajax()) {
            $html = view('memberships.table', compact('memberships'))->render();
            return response()->json(['html' => $html]);
        }

        return view('memberships.index', compact('title', 'subTitle', 'memberships'));
    }

    private function filter(Request $request, $query)
    {
        $filters = [
            'created_at' => ['queryWhere' => '>='],
            'updated_at' => ['queryWhere' => '<='],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
}
