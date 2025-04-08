<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\Http;

class OpenAiService
{
    use LoggableTrait;

    protected $apiKey;
    protected $model = 'gpt-4o-mini';
    protected $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function chat($prompt, $previousMessages = [], $lessonContext = null)
    {
        $messages = [];

        if ($lessonContext) {
            $messages[] = [
                'role' => 'system',
                'content' => "Bạn là trợ lý AI hỗ trợ học tập. Hãy giúp học viên với các câu hỏi liên quan đến bài học sau: {$lessonContext}"
            ];
        } else {
            $messages[] = [
                'role' => 'system',
                'content' => "Bạn là trợ lý AI hỗ trợ học tập. Hãy giúp học viên với các câu hỏi của họ một cách ngắn gọn và dễ hiểu."
            ];
        }

        foreach ($previousMessages as $message) {
            $role = $message['sender_id'] ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => $message['content']
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result['choices'][0]['message']['content'];
            } else {
                return "Xin lỗi, tôi đang gặp vấn đề kỹ thuật. Vui lòng thử lại sau.";
            }
        } catch (\Exception $e) {
            $this->logError($e);

            return "Xin lỗi, tôi đang gặp vấn đề kỹ thuật. Vui lòng thử lại sau.";
        }
    }

    public function saveMessage($conversationId, $senderId, $content, $parentId = null)
    {
        return Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'parent_id' => $parentId,
            'content' => $content,
            'type' => 'text',
        ]);
    }

    public function getOrCreateConversation($userId, $lessonId, $lessonName)
    {
        $conversation = Conversation::where('owner_id', $userId)
            ->where('conversationable_type', 'App\Models\Lesson')
            ->where('conversationable_id', $lessonId)
            ->first();
            
        if (!$conversation) {
            $conversation = Conversation::create([
                'name' => "Hỗ trợ: {$lessonName}",
                'owner_id' => $userId,
                'type' => 'direct',
                'status' => 'active',
                'conversationable_id' => $lessonId,
                'conversationable_type' => 'App\Models\Lesson'
            ]);
            
            $conversation->users()->attach($userId);
        }
        
        return $conversation;
    }
}
