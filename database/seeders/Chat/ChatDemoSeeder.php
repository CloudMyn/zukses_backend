<?php

namespace Database\Seeders\Chat;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationParticipant;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatMessageAttachment;
use App\Models\Chat\ChatMessageReaction;
use App\Models\Chat\ChatMessageStatus;
use App\Models\User;
use App\Models\ShopProfile;

class ChatDemoSeeder extends Seeder
{
    /**
     * Run the database seeds to create a demo chat scenario.
     */
    public function run(): void
    {
        // Create users if they don't exist
        if (User::count() < 5) {
            User::factory()->count(5 - User::count())->create();
        }
        
        // Create shop profiles if they don't exist
        if (ShopProfile::count() < 2) {
            ShopProfile::factory()->count(2 - ShopProfile::count())->create();
        }
        
        // Get users and shop profiles
        $users = User::limit(5)->get();
        $shopProfiles = ShopProfile::limit(2)->get();
        
        // Create a private conversation between two users
        $privateConversation = Conversation::factory()->create([
            'type' => 'private',
            'title' => null,
        ]);
        
        // Create participants for the private conversation
        ConversationParticipant::factory()->create([
            'conversation_id' => $privateConversation->id,
            'user_id' => $users->first()->id,
            'role' => 'participant',
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $privateConversation->id,
            'user_id' => $users->skip(1)->first()->id,
            'role' => 'participant',
        ]);
        
        // Create messages for the private conversation
        $messages = ChatMessage::factory()->count(15)->create([
            'conversation_id' => $privateConversation->id,
            'sender_user_id' => $users->random()->id,
        ]);
        
        // Add reactions to some messages (avoid duplicates)
        foreach ($messages->take(5) as $index => $message) {
            // Create a unique reaction for each message/user combination
            $user = $users->get($index % $users->count());
            ChatMessageReaction::factory()->create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'reaction' => ['👍', '❤️', '😂', '😮', '😢', '👏'][$index % 6],
            ]);
        }
        
        // Add attachments to some messages
        foreach ($messages->take(3) as $message) {
            ChatMessageAttachment::factory()->count(rand(1, 2))->create([
                'message_id' => $message->id,
            ]);
        }
        
        // Create a group conversation
        $groupConversation = Conversation::factory()->create([
            'type' => 'group',
            'title' => 'Team Discussion',
        ]);
        
        // Create participants for the group conversation
        foreach ($users as $index => $user) {
            ConversationParticipant::factory()->create([
                'conversation_id' => $groupConversation->id,
                'user_id' => $user->id,
                'role' => $index === 0 ? 'admin' : 'participant',
            ]);
        }
        
        // Create messages for the group conversation
        ChatMessage::factory()->count(25)->create([
            'conversation_id' => $groupConversation->id,
            'sender_user_id' => $users->random()->id,
        ]);
        
        // Create an order support conversation
        $orderSupportConversation = Conversation::factory()->create([
            'type' => 'order_support',
            'title' => 'Order #12345 Support',
        ]);
        
        // Create participants for the order support conversation
        ConversationParticipant::factory()->create([
            'conversation_id' => $orderSupportConversation->id,
            'user_id' => $users->first()->id,
            'role' => 'participant',
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $orderSupportConversation->id,
            'shop_profile_id' => $shopProfiles->first()->id,
            'role' => 'agent',
        ]);
        
        // Create messages for the order support conversation
        ChatMessage::factory()->count(10)->create([
            'conversation_id' => $orderSupportConversation->id,
            'sender_user_id' => $users->random()->id,
        ]);
    }
}