<?php

namespace App\Http\Controllers;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiChatService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Show the chat interface.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('SecViews.chat');
    }

    public function stream(Request $request, AiChatService $aiChatService)
    {
        set_time_limit(180);

        $validated = $request->validate([
            'conversation_id' => ['required', 'integer'],
            'message' => ['required', 'string', 'max:8000'],
        ]);

        $messageContent = trim($validated['message']);

        if ($messageContent === '') {
            return response()->json(['message' => 'Message cannot be empty.'], 422);
        }

        $conversation = Conversation::where('id', $validated['conversation_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        Message::create([
            'conversation_id' => $conversation->id,
            'content' => $messageContent,
            'is_ai_response' => false,
        ]);

        $conversation->last_activity = now();
        $conversation->message_count = Message::where('conversation_id', $conversation->id)->count();
        $conversation->save();

        return response()->stream(function () use ($aiChatService, $conversation, $messageContent) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            try {
                echo ": connected\n\n";

                if (function_exists('ob_flush')) {
                    @ob_flush();
                }

                flush();

                $fullResponse = trim($aiChatService->streamResponse($messageContent, $conversation->id, function (string $chunk): void {
                    if ($chunk === '') {
                        return;
                    }

                    echo "event: chunk\n";
                    echo 'data: '.json_encode([
                        'text' => $chunk,
                    ], JSON_UNESCAPED_UNICODE)."\n\n";

                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }

                    flush();
                }));

                if ($fullResponse === '') {
                    throw new \RuntimeException('The AI returned an empty response.');
                }

                Message::create([
                    'conversation_id' => $conversation->id,
                    'content' => $fullResponse,
                    'is_ai_response' => true,
                ]);

                $conversation->refresh();
                $conversation->message_count = Message::where('conversation_id', $conversation->id)->count();
                $conversation->last_activity = now();
                $conversation->save();

                echo "event: done\n";
                echo 'data: '.json_encode([
                    'conversationId' => $conversation->id,
                ], JSON_UNESCAPED_UNICODE)."\n\n";

                if (function_exists('ob_flush')) {
                    @ob_flush();
                }

                flush();

                if ($conversation->title === 'New Conversation' && $conversation->message_count >= 4) {
                    $conversation->title = $aiChatService->generateTitleFromChat($conversation->id);
                    $conversation->save();
                }
            } catch (\Throwable $e) {
                Log::error('Chat stream failed', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage(),
                ]);

                echo "event: error\n";
                echo 'data: '.json_encode([
                    'message' => 'Sorry, I encountered an error. Please try again.',
                ], JSON_UNESCAPED_UNICODE)."\n\n";
            } finally {
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }

                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
