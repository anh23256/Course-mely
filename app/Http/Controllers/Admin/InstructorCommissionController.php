<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstructorCommission;
use App\Models\User;
use App\Notifications\Client\InstructorModificationRate;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

use function Laravel\Prompts\note;

class InstructorCommissionController extends Controller
{
    use LoggableTrait, ApiResponseTrait;
    public function index(Request $request)
    {


        $title = 'Quản lý hoa hồng';
        $subTitle = 'Danh sách hoa hồng giảng viên';

        $queryInstructorCommission = InstructorCommission::query()->with('instructor');

        if ($request->hasAny(['id', 'status', 'startDate', 'endDate']))
            $queryInstructorCommission = $this->filter($request, $queryInstructorCommission);

        if ($request->has('query') && $request->query('query')) {
            $searchTerm = $request->query('query');
            $queryInstructorCommission->whereHas('instructor', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%');
            });
        }


        $instructorCommissions = $queryInstructorCommission->paginate(10);

        if ($request->ajax()) {
            $html = view('instructor-commissions.table', compact(['instructorCommissions']))->render();
            return response()->json(['html' => $html]);
        }
        return view('instructor-commissions.index', compact('instructorCommissions', 'title', 'subTitle'));
    }
    public function updateInstructorCommission(Request $request)
    {
        try {
            $rate = $request->input('rate', 0.6);
            $id = $request->input('id', '');

            if($rate > 1 || $rate <= 0){
                return $this->respondError('Hoa hồng mới của giảng viên phải nằm trong khoảng 0 đến 100');
            }

            if (!$id) return $this->respondError('Thông tin không hợp lệ');

            $instructorCommission = InstructorCommission::find($id);

            if (!$instructorCommission) return $this->respondNotFound('Không tìm thấy thông tin');

            $instructorCommission->rate = round($rate,2);
            $logs = json_decode($instructorCommission->rate_logs, true);
            $logs[] = [
                'rate' => round($rate,2),
                'changed_at' => now()
            ];
            $instructorCommission->rate_logs = json_encode($logs);
            $instructorCommission->updated_at = now();
            $instructorCommission->save();

            $instructor = User::where('id', $instructorCommission->instructor_id)->first();
            Log::info($rate*100);

            $instructor->notify(new InstructorModificationRate($rate*100, $instructor));

            return $this->respondOk('Thay đổi hoa hồng của giảng viên thành công', $instructorCommission);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra vui lòng thử lại');
        }
    }
}
