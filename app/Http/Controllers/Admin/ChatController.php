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
        $directConversations = Conversation::whereHas('users', function ($query) {
            $query->where('user_id', auth()->id()); // Kiểm tra người dùng hiện tại có trong nhóm không
        })->where('type', 'direct')->get();

        $groupConversations = Conversation::whereHas('users', function ($query) {
            $query->where('user_id', auth()->id());
        })->where('type', 'group')->get();

        return view(
            'chats.chat-realtime',
            [
                'data' => $data,
                'directConversations' => $directConversations,
                'groupConversations' => $groupConversations
            ]
        );
    }
    public function createPrivateChat(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate dữ liệu đầu vào
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user1 = auth()->id();  // Người dùng đang đăng nhập
            $user2 = $validated['user_id'];  // Người dùng muốn nhắn tin

            // 🛠 Kiểm tra nếu cuộc trò chuyện đã tồn tại
            $existingConversation = Conversation::where('type', 'direct')
                ->whereHas('users', function ($q) use ($user1) {
                    $q->where('user_id', $user1);
                })
                ->whereHas('users', function ($q) use ($user2) {
                    $q->where('user_id', $user2);
                })
                ->first();

            if ($existingConversation) {
                DB::rollBack(); // Không cần tiếp tục giao dịch

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Cuộc trò chuyện đã tồn tại.',
                    'conversation' => $existingConversation // Trả về cuộc trò chuyện cũ nếu đã tồn tại
                ], 400);
            }

            // Nếu chưa tồn tại, tạo mới cuộc trò chuyện
            $conversation = Conversation::create([
                'name' => null,
                'owner_id' => null,
                'type' => 'direct',
                'status' => '1',
            ]);

            // Thêm hai người dùng vào cuộc trò chuyện
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

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể tạo cuộc trò chuyện.'
            ], 500);
        }
    }


    public function createGroupChat(StoreGroupChatRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
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
                $file = $request->file('input_file');
                $filePath = $this->uploadToLocal($file, self::FOLDER);

                $media = Media::create([
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'message_id' => $message->id,
                ]);

                $message->update(['meta_data' => json_encode(['media_id' => $media->id])]);
            }
            $message->load(['sender', 'media']);

            DB::commit();
            // Kiểm tra loại hội thoại (chat nhóm hay chat 1-1)
            if ($message->conversation->type === 'direct') {
                broadcast(new PrivateMessageSent($message))->toOthers();
            } else {
                broadcast(new GroupMessageSent($message))->toOthers();
            }

            $users = ConversationUser::query()->where(['conversation_id' => $validated['conversation_id'], 'is_blocked' => 0])
                ->where('user_id', '<>', auth()->id())->pluck('user_id');

            Notification::send(User::whereIn('id', $users)->get(), new MessageNotification($message));

            return response()->json(['status' => 'success', 'message' => $message]);
        } catch (\Exception $e) {
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
            $query->where('user_id', auth()->id()); // Kiểm tra người dùng hiện tại có trong nhóm không
        })->get();
        $users = User::all();
        $type = Conversation::where('type', 'direct')->get();
        $group = Conversation::where('type', 'group')->get(); // Lọc loại "group"
        return [
            'admins' => $admins,
            'channels' => $channels,
            'users' => $users,
            'type' => $type,
            'group' => $group,
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
                    'status' => Cache::get('user_status_'.$otherUser->id,'offline'),
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
        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender', 'media') // Lấy thông tin người gửi
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'messages' => $messages, 'id' => $conversationId]);
    }
    public function addMembersToGroup(Request $request)
    {
        try {
            // Validate dữ liệu đầu vào
            $validated = $request->validate([
                'group_id' => 'required|exists:conversations,id',  // Kiểm tra nhóm có tồn tại không
                'members' => 'required|array',
                'members.*' => 'exists:users,id',  // Kiểm tra rằng các ID thành viên tồn tại trong bảng users
            ]);

            // Lấy group_id và danh sách members
            $group = Conversation::find($request->group_id);
            $members = $request->members;

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nhóm không tồn tại.',
                ], 404);
            }

            // Lấy danh sách ID thành viên hiện tại của nhóm
            $existingMembers = $group->users->pluck('id')->toArray();

            // Tìm các thành viên đã có trong nhóm
            $duplicateMembers = array_intersect($members, $existingMembers);

            // Nếu có thành viên trùng, trả về danh sách thành viên bị trùng lặp
            if (!empty($duplicateMembers)) {
                $duplicateNames = User::whereIn('id', $duplicateMembers)->pluck('name')->toArray(); // Lấy tên của thành viên
                return response()->json([
                    'success' => false,
                    'message' => 'Một số thành viên đã có trong nhóm.',
                    'duplicate_members' => $duplicateNames, // Gửi danh sách tên thành viên đã có trong nhóm
                ], 400);
            }

            // Thêm thành viên vào nhóm
            $newMembers = array_diff($members, $existingMembers);
            $group->users()->attach($newMembers);

            return response()->json([
                'success' => true,
                'message' => 'Thành viên đã được thêm vào nhóm.',
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm thành viên vào nhóm', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi, vui lòng thử lại sau.',
            ], 500);
        }
    }



    public function getSentFiles($conversationId)
    {
        try {
            $files = Media::whereHas('message', function ($query) use ($conversationId) {
                $query->where('conversation_id', $conversationId);
            })->orderBy('created_at', 'desc')->get();

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
