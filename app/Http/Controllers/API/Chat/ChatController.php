<?php

namespace App\Http\Controllers\API\Chat;

use App\Events\GroupMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
use App\Notifications\Client\MessageNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToLocalTrait;

    const FOLDER_NAME = 'attachments';

    public function apiGetGroupChats(Request $request)
    {
        try {
            $access = $this->checkAccess();

            if ($access !== true) {
                return $access;
            }

            $user = Auth::user();

            $conversations = Conversation::query()
                ->where('owner_id', $user->id)
                ->where('type', 'group')
                ->whereNull('conversationable_id')
                ->whereNull('conversationable_type')
                ->get();

            if (empty($conversations)) {
                return $this->respondNotFound('Người dùng chưa có nhóm nào');
            }

            return $this->respondOk('Danh sách nhóm của người dùng: ' . $user->name, $conversations);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiInfoGroupChat(string $id)
    {
        try {
            $access = $this->checkAccess();

            if ($access !== true) {
                return $access;
            }

            $user = Auth::user();

            $conversation = Conversation::query()
                ->where('owner_id', $user->id)
                ->where('type', 'group')
                ->whereNull('conversationable_id')
                ->whereNull('conversationable_type')
                ->with('users:id,name,avatar')
                ->find($id);

            if (empty($conversation)) {
                return $this->respondNotFound('Không tìm thấy nhóm');
            }

            return $this->respondOk('Thông tin nhóm: ' . $conversation->name, $conversation);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiGetRemainingMembers(string $conversationId)
    {
        try {
            $access = $this->checkAccess();

            if ($access !== true) {
                return $access;
            }

            $user = Auth::user();

            $conversation = $this->getOwnedGroupChat($conversationId);

            $courses = Course::query()->where('user_id', $user->id)->pluck('id');

            $members = CourseUser::query()
                ->whereIn('course_id', $courses)
                ->pluck('user_id')
                ->toArray();

            $memberIds = $conversation->users()->pluck('user_id')->toArray();

            $remainingMembers = User::query()
                ->whereIn('id', $members)
                ->whereNotIn('id', $memberIds)
                ->get();

            return $this->respondOk('Danh sách thành viên chưa có trong nhóm: ' . $conversation->name, $remainingMembers);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiCreateGroupChat(Request $request)
    {
        try {
            DB::beginTransaction();

            $access = $this->checkAccess('instructor');

            if ($access !== true) {
                return $access;
            }

            $user = Auth::user();

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'members' => 'nullable|array',
                'members.*' => 'exists:users,id',
            ]);

            $courses = Course::query()->where('user_id', $user->id)->pluck('id');

            $members = CourseUser::query()
                ->whereIn('course_id', $courses)
                ->pluck('user_id')
                ->toArray();

            $conversation = Conversation::query()->create([
                'name' => $data['name'] ?? 'Nhóm của người dùng: ' . $user->name . Str::uuid(),
                'owner_id' => $user->id,
                'type' => 'group',
                'status' => '1',
            ]);

            $conversation->users()->attach($user->id);

            $validMembers = [];
            if (!empty($data['members']) && is_array($data['members'])) {
                foreach ($data['members'] as $member_id) {
                    if ($member_id != $user->id && in_array($member_id, $members)) {
                        $validMembers[] = $member_id;
                    }
                }
            }

            if (!empty($validMembers)) {
                $conversation->users()->attach($validMembers);
            }

            DB::commit();

            return $this->respondCreated('Tạo nhóm trò chuyện thành công', $conversation);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiAddMemberGroupChat(Request $request, string $id)
    {
        try {
            $conversation = $this->getOwnedGroupChat($id);

            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            $data = $request->validate([
                'members' => 'required|array',
                'members.*' => 'exists:users,id',
            ]);

            $existingMembers = $conversation->users()->pluck('user_id')->toArray();

            $validMembers = array_diff($data['members'], $existingMembers);

            if (empty($validMembers)) {
                return $this->respondBadRequest('Tất cả thành viên đã có trong nhóm');
            }

            $conversation->users()->attach($validMembers);

            return $this->respondOk('Thêm thành viên vào nhóm thành công', $conversation);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiBlockMemberGroupChat(Request $request, string $id, string $memberId)
    {
        try {
            $conversation = $this->getOwnedGroupChat($id);
            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            $memberId = User::query()->find($memberId);

            if (empty($memberId)) {
                return $this->respondNotFound('Không tìm thấy người dùng');
            }

            $conversation->users()->updateExistingPivot($memberId->id, ['is_blocked' => true]);

            return $this->respondOk('Chặn người dùng khỏi nhóm thành công', $conversation);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiUpdateInfoGroupChat(Request $request, string $id)
    {
        try {
            $conversation = $this->getOwnedGroupChat($id);
            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            $data = $request->validate([
                'name' => 'nullable|string|max:255',
            ]);

            $conversation->name = $data['name'] ?? $conversation->name;
            $conversation->save();

            return $this->respondOk('Cập nhật thông tin nhóm thành công', $conversation);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiKickMemberGroupChat(Request $request, string $id, string $memberId)
    {
        try {
            $conversation = $this->getOwnedGroupChat($id);
            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            if (!$conversation->users()->where('user_id', $memberId)->exists()) {
                return $this->respondNotFound('Không tìm thấy thành viên trong nhóm');
            }

            if (!$conversation->users()->where('user_id', $memberId)->exists()) {
                return $this->respondNotFound('Không tìm thấy thành viên trong nhóm');
            }

            if ($memberId == Auth::id()) {
                return $this->respondBadRequest('Bạn là chủ phòng nên không thể xoá bản thân');
            }

            $conversation->users()->detach($memberId);

            return $this->respondOk('Xoá người dùng khỏi nhóm thành công');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiDeleteGroupChat(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $access = $this->checkAccess('instructor');

            if ($access !== true) {
                return $access;
            }

            $conversation = $this->getOwnedGroupChat($id);
            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            $conversation->users()->detach(Auth::id());

            $conversation->delete();

            DB::commit();

            return $this->respondOk('Xoá nhóm trò chuyện thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiGetMessage(Request $request, $conversationId
    )
    {
        try {
            $userId = Auth::id();
            $conversation = Conversation::query()->findOrFail($conversationId);

            if (!$conversation->users->contains($userId)) {
                return $this->respondError('Bạn không thuộc cuộc trò chuyện này');
            }

            $limit = $request->query('limit', 20);
            $page = $request->query('page', 1);

            $messages = Message::query()
                ->where('conversation_id', $conversationId)
                ->with('sender:id,name,avatar')
                ->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);


            return $this->respondOk('Danh sách tin nhắn', $messages);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiSendMessage(Request $request)
    {
        try {
            $data = $request->validate([
                'conversation_id' => 'required|exists:conversations,id',
                'content' => 'required_without:file|string|max:255',
                'type' => 'required|in:text,image,file,video,audio',
//                'parent_id' => 'nullable|exists:messages,id',
                'file' => 'nullable|file|max:10240',
            ]);

            DB::beginTransaction();

            $userId = Auth::id();

            $conversation = Conversation::query()->find($data['conversation_id']);

            if (!$conversation || !($conversation instanceof Conversation)) {
                return $this->respondNotFound('Cuộc trò chuyện không tồn tại');
            }

            if (!$conversation->users->contains($userId)) {
                return $this->respondError('Bạn không thuộc cuộc trò chuyện này');
            }

            $metaData = [
                'read' => false,
                'send_at' => now()
            ];

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
//                'parent_id' => $data['parent_id'] ?? null,
                'content' => $data['content'],
                'type' => $data['type'],
                'meta_data' => json_encode($metaData) ?? null,
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $this->uploadToLocal($file, self::FOLDER_NAME);

                $media = Media::create([
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'message_id' => $message->id,
                ]);

                $message->update(['meta_data' => json_encode(['media_id' => $media->id])]);
            }

            $recipient = $conversation->users->filter(function ($user) {
                return $user->id !== Auth::id();
            })->first();

            if (!$recipient) {
                return $this->respondError('Không có người nhận trong cuộc trò chuyện này');
            }

            if ($this->isUserOnlineInConversation($recipient->id, $conversation->id)) {
                broadcast(new MessageNotification($message, $conversation))->toOthers();
            } else {
                $recipient->notify(new MessageNotification($message, $conversation));
            }

            DB::commit();

//            $this->notifyConversationMembers($conversation, $message);

            return $this->respondOk('Gửi tin nhắn thành công', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiGetOnlineUsers(Request $request)
    {
        try {
            $data = $request->validate([
                'conversation_id' => 'required|exists:conversations,id',
            ]);

            $userId = Auth::id();

            if (!$this->isUserInConversation($userId, $data['conversation_id'])) {
                return $this->respondError('Bản thân không online trong nhóm');
            }

            $conversationId = $data['conversation_id'];
            $cacheKey = "conversation:$conversationId:online_users:$userId";

            Cache::put($cacheKey, true, 300);

            return $this->respondOk('Danh sách user online');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiGetDirectChats(Request $request)
    {
        try {
            $userId = Auth::id();

            $conversations = Conversation::query()
                ->where('type', 'direct')
                ->whereHas('users', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['users:id,name,avatar'])
                ->get();

            if ($conversations->isEmpty()) {
                return $this->respondNotFound('Bạn không có cuộc trò chuyện cá nhân nào');
            }

            $users = $conversations->flatMap(function ($conversation) use ($userId) {
                return $conversation->users->filter(function ($user) use ($userId) {
                    return $user->id !== $userId;
                });
            });

            if ($users->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy người dùng nào trong các cuộc trò chuyện cá nhân');
            }

            $uniqueUsers = $users->unique('id')->values();

            return $this->respondOk('Danh sách cuộc trò chuyện cá nhân', $uniqueUsers);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiStartDirectChat(Request $request)
    {
        try {
            $data = $request->validate([
                'recipient_id' => 'required|exists:users,id',
            ]);

            $recipientId = $data['recipient_id'];
            $userId = Auth::id();

            if ($userId == $recipientId) {
                return $this->respondError('Bạn không thể bắt đầu cuộc trò chuyện với chính mình');
            }

            $conversation = Conversation::query()
                ->where('type', 'direct')
                ->whereHas('users', function ($query) use ($userId, $recipientId) {
                    $query->where('user_id', $userId);
                })
                ->whereHas('users', function ($query) use ($recipientId) {
                    $query->where('user_id', $recipientId);
                })
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'type' => 'direct',
                    'owner_id' => $userId,
                    'name' => 'Cuộc trò chuyện 2 người với nhau ' . Str::uuid()
                ]);

                $conversation->users()->attach([$userId, $recipientId]);
            }

            return $this->respondOk('Tạo trò chuyện thành công', $conversation->load('users'));
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    private function notifyConversationMembers(string $conversation, string $message)
    {
        $senderId = Auth::id();

        $recipientUsers = $conversation->users;

        foreach ($recipientUsers as $user) {
            if ($user->id == $senderId) continue;

            if ($this->isUserOnlineInConversation($user->id, $conversation)) {
                continue;
            }

            $user->notify(new MessageNotification($message));
        }
    }

    private function isUserOnlineInConversation(string $userId, string $conversationId)
    {
        $cacheKey = "conversation:$conversationId:online_users";

        return Cache::has("$cacheKey:$userId");
    }

    private function getOwnedGroupChat(string $id)
    {
        $access = $this->checkAccess('instructor');
        if ($access !== true) {
            return $access;
        }

        $user = Auth::user();

        $conversation = Conversation::query()
            ->where('owner_id', $user->id)
            ->where('type', 'group')
            ->whereNull('conversationable_id')
            ->whereNull('conversationable_type')
            ->find($id);

        if (empty($conversation)) {
            return $this->respondNotFound('Không tìm thấy nhóm');
        }

        return $conversation;
    }

    protected function checkAccess(?string $role = null)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnauthorized('Bạn không có quyền truy cập');
        }

        if ($role && !$user->hasRole($role)) {
            return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
        }

        return true;
    }
}
