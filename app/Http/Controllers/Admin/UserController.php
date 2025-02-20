<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\User;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Stmt\Return_;

class UserController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, FilterTrait;

    const FOLDER = 'users';
    const URLIMAGEDEFAULT = "https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png";


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $title = 'Quản lý thành viên';
            $subTitle = 'Danh sách người dùng';
            session()->forget('nameRouteUser');
            $roleUser = $this->getRoleFromSegment();
            $actorRole = $roleUser['role_name'];
            session(['nameRouteUser' => $roleUser]);

            $queryUsers = User::query()->latest('id')->with('profile');

            $queryUserCounts = User::query()
                ->selectRaw('
                    count(id) as total_users,
                    sum(status = "active") as active_users,
                    sum(status = "inactive") as inactive_users,
                    sum(status = "blocked") as blocked_users
                ');

            if ($request->hasAny(['code', 'name', 'email', 'profile_phone_user', 'status', 'created_at', 'updated_at', 'start_deleted', 'end_deleted'])) {
                $queryUsers = $this->filter($request, $queryUsers);
            }

            if ($request->has('search_full')) {
                $queryUsers = $this->search($request->search_full, $queryUsers);
            }

            if ($roleUser['name'] === 'deleted') {
                $queryUsers->with('roles:name')->onlyTrashed();
                $queryUserCounts->onlyTrashed();
            } else {
                $queryUsers->whereHas('roles', function ($query) use ($roleUser) {
                    $query->where('name', $roleUser['name']);
                });

                $queryUserCounts->whereHas('roles', function ($query) use ($roleUser) {
                    $query->where('name', $roleUser['name']);
                });
            }

            $users = $queryUsers->paginate(10);
            $userCounts = $queryUserCounts->first();

            if ($request->ajax()) {
                $html = view('users.table', compact(['users', 'roleUser', 'actorRole']))->render();
                return response()->json(['html' => $html]);
            }

            return view('users.index', compact(['users', 'userCounts', 'subTitle', 'title', 'roleUser', 'actorRole']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $title = 'Quản lý thành viên';
            $subTitle = 'Thêm mới người dùng';

            $roles = Role::query()->get()->pluck('name')->toArray();

            return view('users.create', compact([
                'title',
                'subTitle',
                'roles'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->except('avatar');

            if ($request->hasFile('avatar')) {
                $urlAvatar = $this->uploadImage($request->file('avatar'), self::FOLDER);
            }

            do {
                $data['code'] = str_replace('-', '', Str::uuid()->toString());
            } while (User::query()->where('code', $data['code'])->exists());

            $data['avatar'] = $urlAvatar ?? self::URLIMAGEDEFAULT;

            $data['email_verified_at'] = now();

            $user =  User::query()->create($data);

            $user->assignRole($request->role);

            DB::commit();

            $routeUserByRole = $request->role === 'employee' ? 'employees'
                : ($request->role === 'instructor' ? 'instructors' : 'clients');

            return redirect()->route('admin.' . $routeUserByRole . '.index')->with('success', 'Thêm mới thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($urlAvatar) && filter_var($urlAvatar, FILTER_VALIDATE_URL)) {
                $this->deleteImage($urlAvatar, self::FOLDER);
            }

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {

            $title = 'Quản lý thành viên';
            $subTitle = 'Chi tiết người dùng';

            $user->load('profile');

            return view('users.show', compact([
                'user',
                'title',
                'subTitle'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        try {

            $title = 'Quản lý thành viên';
            $subTitle = 'Cập nhật người dùng';

            $roles = Role::query()->get()->pluck('name')->toArray();

            return view('users.edit', compact([
                'user',
                'title',
                'subTitle',
                'roles'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $validator = $request->validated();

            $data = $request->except('avatar');

            DB::beginTransaction();

            $currencyAvatar = $user->avatar;

            if ($request->hasFile('avatar')) {
                $data['avatar'] = $this->uploadImage($request->file('avatar'), self::FOLDER);
            }

            $data['email_verified_at'] = !empty($data['email_verified']) ? now() : null;

            $user->update($data);

            if ($request->has('role')) {
                $user->syncRoles([]);

                $user->assignRole($request->input('role'));
            }

            if (
                isset($data['avatar']) && !empty($data['avatar'])
                && filter_var($data['avatar'], FILTER_VALIDATE_URL)
                && !empty($currencyAvatar) && $currencyAvatar !== self::URLIMAGEDEFAULT
            ) {
                $this->deleteImage($currencyAvatar, self::FOLDER);
            }

            DB::commit();

            return redirect()->route('admin.users.edit', $user)->with('success', 'Cập nhật thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['avatar']) && !empty($data['avatar']) && filter_var($data['avatar'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['avatar']);
            }

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
    public function import(Request $request, string $role = null)
    {
        try {
            $startTime = microtime(true);
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,csv',
            ], [
                'file.required' => 'Bắt buộc tải file',
                'file.mimes' => 'File tải lên phải thuộc loại xlsx hoặc csv',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator);
            }

            $validRoles = Role::pluck('name')->toArray();
            $role = in_array($role, $validRoles) ? $role : 'member';

            $import = new UsersImport($role);
            Excel::import($import, $request->file('file'));

            $endTime = microtime(true);

            Log::info(' Thời gian import: ' . ($endTime - $startTime));

            return redirect()->back()->with('success', 'Import thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }
    }
    public function export(string $role = null)
    {
        try {
            
            $validRoles = Role::pluck('name')->toArray();
            $role = in_array($role, $validRoles) ? $role : 'member';

            return Excel::download(new UsersExport($role), 'Users_' . $role . '.xlsx');

        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $userID = explode(',', $id);

                $this->deleteUsers($userID);
            } else {
                $user = User::query()->findOrFail($id);

                $data['avatar'] = $user->avatar;

                $user->delete();
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

    public function updateEmailVerified(Request $request, User $user)
    {
        try {
            $data['email_verified_at'] = !empty($request->input('email_verified')) ? now() : null;

            $user->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thành công'
            ]);
        } catch (\Exception $e) {

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Cập nhật thất bại'
            ]);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $userID = explode(',', $id);

                $this->deleteUsers($userID);
            } else {
                $user = User::query()->onlyTrashed()->findOrFail($id);

                $data['avatar'] = $user->avatar;

                $user->forceDelete();

                if (
                    isset($data['avatar']) && !empty($data['avatar']) && filter_var($data['avatar'], FILTER_VALIDATE_URL)
                    && $data['avatar'] !== self::URLIMAGEDEFAULT
                ) {
                    $this->deleteImage($data['avatar'], self::FOLDER);
                }
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

    public function restoreDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $userID = explode(',', $id);

                $this->restoreDeleteUsers($userID);
            } else {
                $user = User::query()->onlyTrashed()->findOrFail($id);

                if ($user->trashed()) {
                    $user->restore();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Khôi phục thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Khôi phục thất bại'
            ]);
        }
    }

    private function deleteUsers(array $userID)
    {

        $users = User::query()->whereIn('id', $userID)->withTrashed()->get();

        foreach ($users as $user) {

            $avatar = $user->avatar;

            if ($user->trashed()) {
                $user->forceDelete();

                if (
                    isset($avatar) && !empty($avatar)
                    && filter_var($avatar, FILTER_VALIDATE_URL)
                    && $avatar !== self::URLIMAGEDEFAULT
                ) {
                    $this->deleteImage($avatar, self::FOLDER);
                }
            } else {
                $user->delete();
            }
        }
    }
    private function restoreDeleteUsers(array $userID)
    {

        $users = User::query()->whereIn('id', $userID)->onlyTrashed()->get();

        foreach ($users as $user) {

            $avatar = $user->avatar;

            if ($user->trashed()) {
                $user->restore();
            }
        }
    }

    private function getRoleFromSegment()
    {
        $role = request()->segment(3);

        $role = explode('-', $role)[1];

        $roles = [
            'clients' => ['name' => 'member', 'actor' => 'khách hàng', 'role_name' => 'clients'],
            'instructors' => ['name' => 'instructor', 'actor' => 'người hướng dẫn', 'role_name' => 'instructors'],
            'employees' => ['name' => 'employee', 'actor' => 'nhân viên', 'role_name' => 'employees'],
            'deleted' => ['name' => 'deleted', 'actor' => 'thành viên đã xóa', 'role_name' => 'users.deleted']
        ];

        return $roles[$role] ?? ['name' => 'member', 'actor' => 'khách hàng', 'role_name' => 'clients'];
    }

    private function filter(Request $request, $query)
    {
        $filters = [
            'created_at' => ['queryWhere' => '>='],
            'updated_at' => ['queryWhere' => '<='],
            'deleted_at' => ['attribute' => ['start_deleted' => '>=', 'end_deleted' => '<=']],
            'code' => ['queryWhere' => 'LIKE'],
            'name' => ['queryWhere' => 'LIKE'],
            'email' => ['queryWhere' => 'LIKE'],
            'status' => ['queryWhere' => '='],
            'profile_phone_user' => null,
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }

    private function search($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            $query->whereHas('profile', function ($query) use ($searchTerm) {
                $query->where('phone', 'LIKE', "%$searchTerm%");
            })
                ->orWhere('name', 'LIKE', "%$searchTerm%")
                ->orWhere('email', 'LIKE', "%$searchTerm%")
                ->orWhere('code', 'LIKE', "%$searchTerm%");
        }

        return $query;
    }
}
