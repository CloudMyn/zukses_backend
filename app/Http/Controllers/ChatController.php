<?php

namespace App\Http\Controllers;

use App\Models\Chat\Conversation;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ConversationParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Get all conversations for current user
    public function getConversations()
    {
        $user = Auth::user();

        $conversations = Conversation::where(function($query) use ($user) {
            $query->where('owner_user_id', $user->id)
                  ->orWhereHas('participants', function($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
        })
        ->with(['lastMessage', 'participants.user', 'participants.shopProfile'])
        ->orderBy('last_message_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    // Get messages in a conversation
    public function getMessages($conversationId)
    {
        $user = Auth::user();

        // Check if user is participant in conversation
        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant && !Conversation::where('id', $conversationId)->where('owner_user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        $messages = ChatMessage::where('conversation_id', $conversationId)
            ->with(['senderUser', 'senderShopProfile', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    // Send a message
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string',
        ]);

        $user = Auth::user();

        // Check if user is participant in conversation
        $participant = ConversationParticipant::where('conversation_id', $request->conversation_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant && !Conversation::where('id', $request->conversation_id)->where('owner_user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to conversation'
            ], 403);
        }

        $message = ChatMessage::create([
            'conversation_id' => $request->conversation_id,
            'sender_user_id' => $user->id,
            'content' => $request->content,
            'content_type' => 'text',
        ]);

        // Update conversation last message
        $conversation = Conversation::find($request->conversation_id);
        $conversation->last_message_id = $message->id;
        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'success' => true,
            'data' => $message->load(['senderUser', 'senderShopProfile'])
        ]);
    }

    // Create a new conversation
    public function createConversation(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:private,group,order_support,product_support,system',
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:users,id'
        ]);

        $user = Auth::user();

        $conversation = Conversation::create([
            'title' => $request->title,
            'type' => $request->type,
            'owner_user_id' => $user->id,
            'is_open' => true,
        ]);

        // Add participants
        foreach ($request->participant_ids as $participantId) {
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $participantId,
                'role' => 'participant',
                'joined_at' => now(),
            ]);
        }

        // Add creator as participant
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $conversation->load(['participants.user'])
        ]);
    }
}