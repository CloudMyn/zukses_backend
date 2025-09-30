<?php

namespace Database\Seeders\Chat;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationParticipant;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatMessageStatus;
use App\Models\User;
use App\Models\ShopProfile;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample conversations
        $conversations = Conversation::factory()->count(5)->create();
        
        // Create participants for each conversation
        foreach ($conversations as $conversation) {
            // Add 2-5 participants to each conversation
            $participantsCount = rand(2, 5);
            ConversationParticipant::factory()->count($participantsCount)->create([
                'conversation_id' => $conversation->id,
            ]);
        }
        
        // Create messages for each conversation
        foreach ($conversations as $conversation) {
            // Add 10-30 messages to each conversation
            $messagesCount = rand(10, 30);
            $messages = ChatMessage::factory()->count($messagesCount)->create([
                'conversation_id' => $conversation->id,
            ]);
            
            // Create statuses for each message
            foreach ($messages as $message) {
                // Get participants of this conversation
                $participants = ConversationParticipant::where('conversation_id', $conversation->id)->get();
                
                // Create a status for each participant
                foreach ($participants as $participant) {
                    if ($participant->user_id) {
                        ChatMessageStatus::factory()->create([
                            'message_id' => $message->id,
                            'user_id' => $participant->user_id,
                            'status' => 'read',
                        ]);
                    }
                }
            }
        }
    }
}