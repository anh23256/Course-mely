<?php

namespace App\Http\Controllers\Admin;

use App\Events\GroupMessageSent;
use App\Events\PrivateMessageSent;
use App\Events\UserStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Chats\StoreGroupChatRequest;
use App\Http\Requests\Admin\Chats\StoreSendMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Group;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageNotification;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Psr\Log\LoggerTrait;

class ChatController extends Controller
{
    use LoggableTrait, UploadToLocalTrait;

    const FOLDER = "messages";

    public function index()
    {
        $data = $this->getAdminsAndChannels();

        return view(
            'chats.chat-realtime',
            [
                'data' => $data,
            ]
        );
    }
    public function createPrivateChat(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user1 = auth()->id();
            $user2 = $validated['user_id'];

            // Kiểm tra xem cuộc trò chuyện 1-1 đã tồn tại chưa
            $conversation = Conversation::where('type', 'direct')
                ->whereHas('users', function ($q) use ($user1) {
                    $q->where('user_id', $user1);
                })
                ->whereHas('users', function ($q) use ($user2) {
                    $q->where('user_id', $user2);
                })
                ->first();

            if ($conversation) {
                $data = $this->getAdminsAndChannels();
                $data['conversation'] = $conversation;
                DB::commit();
                return response()->json([
                    'status' => 'failed',
                    'data' => $data
                ]);
            }
            $conversation = Conversation::create([
                'name' => null, // Không cần tên nhóm trong chat 1-1
                'owner_id' => null, // Không cần owner trong chat 1-1
                'type' => 'direct',
                'status' => '1',
            ]);
            $conversation->users()->attach([$user1, $user2]);
            $data = $this->getAdminsAndChannels();
            $data['conversation'] = $conversation;
            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, $request->all());
            return response()->json(['status' => 'error', 'message' => 'Không thể tạo cuộc trò chuyện']);
        }
    }

    public function createGroupChat(StoreGroupChatRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Kiểm tra tổng số thành viên (bao gồm cả owner)
            if (count($validated['members']) + 1 < 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nhóm phải có ít nhất 3 người.',
                ], 422);
            }
            // Tạo nhóm chat
            $conversation = Conversation::create([
                'name' => $validated['name'] ?? 'Nhóm ẩn danh',
                'owner_id' => auth()->id(),
                'type' => 'group',
                'status' => '1',
                'conversationable_id' => null,
                'conversationable_type' => null,
            ]);
            $conversation->users()->attach(auth()->id());

            if ($request->has('members') && is_array($request->members)) {
                foreach ($request->members as $member_id) {
                    if ($member_id == auth()->id()) {
                        continue; // Bỏ qua owner
                    }

                    $user = User::find($member_id);
                    if ($user) {
                        $conversation->users()->attach($member_id);
                    }
                }
            }
            DB::commit();
            $data = $this->getAdminsAndChannels();
            $data['conversation'] = $conversation;

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm nhóm thành công',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, $request->all());

            return response()->json([
                'status' => 'error',
                'message' => 'Thao tác không thành công',
            ]);
        }
    }

    public function sendGroupMessage(StoreSendMessageRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            $conversation = Conversation::findOrFail($validated['conversation_id']);

            // Xác định người nhận: lấy tất cả người trong cuộc trò chuyện, trừ sender_id
            $received = $conversation->users()
                ->where('user_id', '<>', auth()->id()) // Lấy người khác (không phải sender)
                ->first(); // Vì chat 1-1 chỉ có 1 người còn lại

            if (!$received) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy người nhận']);
            }
            $message = Message::create([
                'conversation_id' => $validated['conversation_id'],
                'sender_id' => auth()->id(),
                'parent_id' => $validated['parent_id'] ?? null,

                'content' => $validated['content'] ?? null,
                'type' => $validated['type'],
                'meta_data' => $validated['meta_data'] ?? null,
            ]);

            if ($request->hasFile('input_file')) {
                $files = $request->file('input_file');
                $filePaths = $this->uploadMultiple($files, self::FOLDER);

                if (!$filePaths) {
                    return;
                }

                foreach ($files as $index => $file) {
                    if (!isset($filePaths[$index])) {
                        continue;
                    }
                    $file_path = $filePaths[$index];
                    $file_type = $file->getClientMimeType();
                    $file_size = $file->getSize();
                    $file_name = $file->getClientOriginalName();

                    $media = Media::create([
                        'file_path' => $file_path,
                        'file_type' => $file_type,
                        'file_size' => $file_size,
                        'message_id' => $message->id,
                    ]);

                    $mediaData[] = [
                        'media_id' => $media->id,
                        'file_name' => $file_name,
                        'file_path' => $file_path,
                        'file_type' => $file_type,
                        'file_size' => $file_size,
                    ];
                }

                $message->update(['meta_data' => $mediaData]);
            }

            $message->load(['sender', 'media']);

            DB::commit();

            if ($message->conversation->type === 'direct') {
                broadcast(new PrivateMessageSent($message))->toOthers();
            } else {
                broadcast(new GroupMessageSent($message))->toOthers();
            }

            $users = ConversationUser::query()->where(['conversation_id' => $validated['conversation_id'], 'is_blocked' => 0])
                ->where('user_id', '<>', auth()->id())->pluck('user_id');

            Notification::send(User::whereIn('id', $users)->get(), new MessageNotification($message));

            return response()->json(['status' => 'success', 'message' => $message]);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $this->logError($e, $request->all());
        }
    }

    protected function getAdminsAndChannels()
    {
        $roleUser = ['employee', 'admin'];
        $admins = User::whereHas('roles', function ($query) use ($roleUser) {
            $query->whereIn('name', $roleUser);
        })->where('id', '!=', auth()->id())->get();

        $channels = Conversation::whereHas('users', function ($query) {
            $query->where('user_id', auth()->id());
        })->orderByDesc(
            Message::whereColumn('conversation_id', 'conversations.id')
                ->select('created_at')
                ->latest()
                ->take(1)
        )->limit(50)->get();

        $firstChanel = optional($channels->select(['type', 'id'])->first());

        $users = User::all();
        $type = $channels->where('type', 'direct');
        $group = $channels->where('type', 'group');

        return [
            'admins' => $admins,
            'channels' => $channels,
            'users' => $users,
            'type' => $type,
            'group' => $group,
            'firstChanel' => $firstChanel
        ];
    }
    public function getGroupInfo(Request $request)
    {
        try {
            $groupId = $request->id;
            $group = Conversation::findOrFail($groupId);
            $name = $group->name;
            $memberCount = $group->users()->count() . ' thành viên';
            $member = $group->users()->select('user_id', 'name', 'avatar')->get();
            $leader = User::find($group->owner_id);
            $channelId = $group->id;

            // Trả về thông tin nhóm
            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $name,  // Tên nhóm
                    'memberCount' => $memberCount ?? null, // Số thành viên
                    'member' => $member ?? null,
                    'group' => $group,
                    'leader' => $leader ?? null,
                    'avatar' => $avatar ?? url('assets/images/users/multi-user.jpg'),
                    'channelId' => $channelId,
                ]
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy thông tin nhóm'
            ]);
        }
    }
    public function getUserInfo(Request $request)
    {
        try {
            $groupId = $request->id;
            $conversation = Conversation::findOrFail($groupId);
            $currentUserId = auth()->id();
            $otherUser = $conversation->users->where('id', '<>', $currentUserId)->first();

            if (!$otherUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy người còn lại trong cuộc trò chuyện.'
                ]);
            }
            $name = $otherUser->name;
            $avatar = $otherUser->avatar ?? url('assets/images/users/user-dummy-img.jpg');
            $memberCount = null; // Không cần hiển thị số thành viên
            // Trả về thông tin nhóm
            return response()->json([
                'status' => 'success',
                'data' => [
                    'nameUser' => $name,
                    'direct' => $conversation,
                    'avatarUser' => $avatar,
                    'channelId' => $conversation->id,
                    'memberCount' => $memberCount,
                    'sender_id' => $otherUser->id
                ]
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy thông tin nhóm'
            ]);
        }
    }
    public function getGroupMessages($conversationId)
    {
        $messages =  Message::where('conversation_id', $conversationId)
            ->with('sender', 'media')
            ->latest()
            ->limit(80)->get()->sortBy('created_at')->values()->toArray();

        return response()->json(['status' => 'success', 'messages' => $messages, 'id' => $conversationId]);
    }
    public function addMembersToGroup(Request $request)
    {
        $validated = $request->validate([
            'members' => 'required|array',
            'members.*' => 'exists:users,id',  // Kiểm tra rằng các ID thành viên tồn tại trong bảng users 
        ]);

        // Lấy group_id và members
        $group = Conversation::find($request->group_id);
        $members = $request->members;
        $existingMembers = $group->users->pluck('id')->toArray();  // Lấy ID thành viên hiện tại của nhóm
        $newMembers = array_diff($members, $existingMembers);  // Lọc ra các thành viên chưa có trong nhóm

        // Kiểm tra xem có thành viên nào đã tồn tại trong nhóm hay không
        $alreadyExists = array_intersect($members, $existingMembers);

        if (count($alreadyExists) > 0) {
            // Lấy tên các thành viên đã tồn tại
            $duplicateMembers = User::whereIn('id', $alreadyExists)->pluck('name')->toArray();

            return response()->json([
                'success' => false,
                'message' => 'Các thành viên sau đã có trong nhóm.',
                'duplicate_members' => $duplicateMembers,  // Trả về danh sách tên thành viên trùng lặp
            ], 400);
        }
        // Thêm thành viên vào nhóm (giả sử nhóm có quan hệ many-to-many với users)
        foreach ($newMembers as $memberId) {
            $group->users()->attach($memberId);  // Thêm thành viên vào nhóm
        }

        return response()->json([
            'success' => true,
            'message' => 'Thành viên đã được thêm vào nhóm.',
        ]);
    }
    public function getSentFiles($conversationId)
    {
        try {
            $files = Message::where('conversation_id', $conversationId)
                ->whereNotNull('meta_data')
                ->whereRaw("JSON_VALID(meta_data) AND JSON_LENGTH(meta_data) > 0")
                ->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'files' => $files
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }
    public function leaveConversation($conversationId)
    {
        try {
            $user = auth()->user();  // Lấy người dùng hiện tại
            $conversation = Conversation::findOrFail($conversationId);

            // Kiểm tra nếu người dùng có trong cuộc trò chuyện
            if ($conversation->users->contains($user)) {
                // Kiểm tra nếu người rời nhóm là trưởng nhóm (owner)
                if ($conversation->owner_id == $user->id) {
                    // Kiểm tra nếu chỉ còn một người tham gia trong nhóm
                    if ($conversation->users->count() > 1) {
                        // Tìm người tham gia tiếp theo trong nhóm để làm chủ nhóm mới (owner mới)
                        $newOwner = $conversation->users()->where('user_id', '!=', $user->id)->first();  // Chọn người tham gia đầu tiên không phải trưởng nhóm
                        $conversation->owner_id = $newOwner->id;  // Cập nhật chủ nhóm mới

                        // Lưu lại thay đổi
                        $conversation->save();
                    } else {
                        // Nếu chỉ còn một người trong nhóm, không thể chuyển quyền
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Không thể rời nhóm, nhóm này chỉ còn bạn là thành viên.',
                        ]);
                    }
                }

                // Xóa liên kết giữa người dùng và cuộc trò chuyện
                $conversation->users()->detach($user->id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Bạn đã rời khỏi cuộc trò chuyện này.',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không phải là thành viên của cuộc trò chuyện này.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
            ]);
        }
    }

    public function deleteConversation($conversationId)
    {
        try {
            $user = auth()->user();  // Lấy người dùng hiện tại
            $conversation = Conversation::findOrFail($conversationId);

            // Kiểm tra nếu cuộc trò chuyện là 1-1 (chỉ có 2 người tham gia)
            if ($conversation->users()->count() == 2) {
                // Kiểm tra nếu người dùng là một trong hai người tham gia cuộc trò chuyện
                if ($conversation->users->contains($user)) {
                    // Xóa tất cả liên kết người dùng với cuộc trò chuyện
                    $conversation->users()->detach();

                    // Xóa cuộc trò chuyện
                    $conversation->delete();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Cuộc trò chuyện đã bị xóa.',
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Bạn không có quyền xóa cuộc trò chuyện này.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cuộc trò chuyện này không phải là cuộc trò chuyện 1-1.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
            ]);
        }
    }

    public function statusUser(Request $request)
    {
        try {
            $user = Auth::user();
            $status = $request->status;

            if (!$user) {
                return;
            }

            Cache::put("user_status_$user->id", $status, now()->addMinutes(2));
            Cache::put("last_activity_$user->id", now(), now()->addMinutes(2));

            Broadcast(new UserStatusChanged($user->id, $status))->toOthers();

            return response()->json(['success' => true, 'status' => $status]);
        } catch (\Throwable $e) {
            $this->logError($e);

            return;
        }
    }
    public function kickUserFromGroup(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate dữ liệu đầu vào
            $validated = $request->validate([
                'group_id' => 'required|exists:conversations,id',
                'user_id' => 'required|exists:users,id',
            ]);

            $group = Conversation::find($validated['group_id']);
            $userToKick = User::find($validated['user_id']);
            $admin = auth()->user(); // Người đang thực hiện thao tác

            if (!$group || !$userToKick) {
                return response()->json(['success' => false, 'message' => 'Nhóm hoặc người dùng không tồn tại.'], 404);
            }

            // Kiểm tra nếu người gọi API là admin hoặc chủ nhóm
            if ($group->owner_id !== $admin->id && !$group->admins->contains($admin->id)) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này.'], 403);
            }

            // Không thể kick chủ nhóm
            if ($group->owner_id == $userToKick->id) {
                return response()->json(['success' => false, 'message' => 'Không thể kick chủ nhóm.'], 403);
            }
                // Kiểm tra số lượng thành viên trước khi kick
            $currentMemberCount = $group->users()->count();

            if ($currentMemberCount <= 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nhóm phải có ít nhất 2 thành viên. Không thể tiếp tục xóa thêm.'
                ], 422);
            }
            // Xóa người dùng khỏi nhóm
            $group->users()->detach($userToKick->id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Người dùng đã bị kick khỏi nhóm.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi kick người dùng khỏi nhóm', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.'], 500);
        }
    }
    public function dissolveGroup(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate dữ liệu đầu vào
            $validated = $request->validate([
                'group_id' => 'required|exists:conversations,id',
            ]);

            $group = Conversation::find($validated['group_id']);
            $admin = auth()->user(); // Người thực hiện thao tác

            if (!$group) {
                return response()->json(['success' => false, 'message' => 'Nhóm không tồn tại.'], 404);
            }

            // Kiểm tra nếu người gọi API là chủ nhóm
            if ($group->owner_id !== $admin->id) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền giải tán nhóm.'], 403);
            }

            // Xóa tất cả thành viên khỏi nhóm
            $group->users()->detach();

            // Xóa nhóm
            $group->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Nhóm đã được giải tán.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi giải tán nhóm', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.'], 500);
        }
    }
}
