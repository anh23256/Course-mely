<?php

namespace App\Http\Controllers\API\Instructor;

use App\Events\LiveChatMessageSent;
use App\Events\UserJoinedLiveSession;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\LiveSession;
use App\Models\LiveSessionParticipant;
use App\Models\Message;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LivestreamController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index()
    {
        try {
            $liveSessions = LiveSession::query()
                ->with('instructor')
                ->where(function ($query) {
                    $query->where('status', 'Đang diễn ra')
                        ->orWhere(function ($query) {
                            $query->where('status', 'Sắp diễn ra')
                                ->where('start_time', '>', now());
                        });
                })
                ->orderBy('start_time', 'asc')
                ->get();

            if (empty($liveSessions)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            return $this->respondOk('Danh sách phiên live trên hệ thống', $liveSessions);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function show(string $id)
    {
        try {
            $lessonSessionInfo = LiveSession::query()
                ->with([
                    'instructor',
                    'conversation.messages' => function ($query) {
                        $query->orderBy('created_at', 'asc')->limit(20);
                    },
                    'participants' => function ($query) {
                        $query->select('user_id', 'live_session_id', 'role');
                    },
                    'conversation.users' => function ($query) {
                        $query->select('conversation_id', 'user_id', 'is_blocked');
                    },
                ])
                ->where(function ($query) {
                    $query->where('status', 'Đang diễn ra')
                        ->orWhere(function ($query) {
                            $query->where('status', 'Sắp diễn ra')
                                ->where('start_time', '>', now());
                        });
                })
                ->find($id);

            if (empty($lessonSessionInfo)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            return $this->respondOk('Thông tin phiên live', $lessonSessionInfo);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function getLivestreams(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $query = LiveSession::query()
                ->where('instructor_id', $user->id)
                ->orderBy('start_time', 'desc');

            if ($request->has('fromDate')) {
                $query->whereDate('created_at', '>=', $request->input('fromDate'));
            }
            if ($request->has('toDate')) {
                $query->whereDate('created_at', '<=', $request->input('toDate'));
            }

            $liveSessions = $query->get();

            if (empty($liveSessions)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            return $this->respondOk('Danh sách phiên live của: ' . $user->name, $liveSessions);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function startLivestream(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $existingLiveSession = LiveSession::query()
                ->where('instructor_id', $user->id)
                ->whereIn('status', ['Đang diễn ra', 'Sắp diễn ra'])
                ->first();

            if ($existingLiveSession) {
                return $this->respondError('Bạn đang có phiên live chưa kết thúc');
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'nullable|date',
            ]);

            $stream = $this->liveStream($validated['title']);

            $data = [
                'instructor_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_time' => $validated['start_time'] ?? now(),
                'stream_key' => $stream['stream_key'],
                'mux_playback_id' => $stream['playback_id'],
            ];

            $liveSession = LiveSession::query()->create($data);

            $conversation = Conversation::query()->firstOrCreate([
                'owner_id' => Auth::id(),
                'name' => $validated['title'],
                'type' => 'group',
                'status' => 1,
                'conversationable_type' => LiveSession::class,
                'conversationable_id' => $liveSession->id,
            ]);

            LiveSessionParticipant::query()->create([
                'user_id' => $user->id,
                'live_session_id' => $liveSession->id,
                'role' => 'host',
                'joined_at' => now()
            ]);

            ConversationUser::query()->firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'is_blocked' => false,
                'last_read_at' => now()
            ]);

            DB::commit();
            return $this->respondCreated('Tạo phiên live thành công', $liveSession);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function joinLiveSession($liveSessionId)
    {
        try {
            $liveSession = LiveSession::query()
                ->with('instructor')
                ->find($liveSessionId);

            if (empty($liveSession)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            $user = Auth::check() ? Auth::user() : null;

            if (!$user) {
                broadcast(new UserJoinedLiveSession($liveSessionId, null));

                return $this->respondOk('Xem phiên live thành công', [
                    'live_session' => $liveSession,
                    'user' => null
                ]);
            }

            $conversation = Conversation::query()
                ->where('conversationable_type', LiveSession::class)
                ->where('conversationable_id', $liveSessionId)
                ->first();

            if (empty($conversation)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            $existingParticipant = LiveSessionParticipant::query()->where([
                'user_id' => $user->id,
                'live_session_id' => $liveSessionId
            ])->first();

            if (!$existingParticipant) {
                LiveSessionParticipant::create([
                    'user_id' => $user->id,
                    'live_session_id' => $liveSessionId,
                    'role' => 'viewer',
                    'joined_at' => now()
                ]);
            }

            $existingConversationUser = ConversationUser::query()->where([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id
            ])->first();

            if (!$existingConversationUser) {
                ConversationUser::query()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                    'is_blocked' => false,
                    'last_read_at' => now()
                ]);
            }

            broadcast(new UserJoinedLiveSession($liveSessionId, $user));

            return $this->respondOk('Tham gia phiên live thành công', [
                'live_session' => $liveSession,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                ] : null
            ]);

        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function sendMessage(Request $request, $liveSessionId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn cần đăng nhập để thực hiện chức năng này');
            }

            $validated = $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            $liveSession = LiveSession::query()->find($liveSessionId);

            $participant = LiveSessionParticipant::where([
                'user_id' => $user->id,
                'live_session_id' => $liveSession->id
            ])->first();

            if (!$participant) {
                return response()->json([
                    'error' => 'Bạn chưa tham gia phiên live này'
                ], 403);
            }

            $conversation = Conversation::firstOrCreate(
                [
                    'conversationable_type' => LiveSession::class,
                    'conversationable_id' => $liveSession->id
                ],
                [
                    'name' => $validated['title'] ?? 'Phiên live',
                    'type' => 'group',
                    'status' => 1,
                    'owner_id' => $user->id,
                    'conversationable_type' => LiveSession::class,
                    'conversationable_id' => $liveSession->id,
                ]
            );


            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'content' => $validated['message'],
                'type' => 'text',
                'meta_data' => null
            ]);

            broadcast(new LiveChatMessageSent($message, $user, $liveSessionId))->toOthers();

            return $this->respondOk('Gửi tin nhắn thành công', $message);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function liveStream($streamName)
    {
        $httpClient = new \GuzzleHttp\Client();

        $response = $httpClient->post('https://api.mux.com/video/v1/live-streams', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(env('MUX_TOKEN_ID') . ':' . env('MUX_TOKEN_SECRET')),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'playback_policy' => ['public'],
                'new_asset_settings' => ['playback_policy' => 'public'],
                'name' => $streamName,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'stream_key' => $data['data']['stream_key'],
            'playback_id' => $data['data']['playback_ids'][0]['id'],
        ];
    }

    protected function createTemporaryUser($liveSessionId)
    {
        return User::query()->create([
            'code' => Str::random(10),
            'name' => 'Khách ' . Str::random(5),
            'email' => Str::random(10) . '@temporary.coursemely.com',
            'password' => Str::random(10),
            'is_temporary' => true,
            'temp_live_session_id' => $liveSessionId
        ]);
    }
}
