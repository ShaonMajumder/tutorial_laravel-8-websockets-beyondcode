<?php

namespace Tests\Feature\Broadcast;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Events\NewMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Broadcast;

class NewMessageTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function test_example()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    public function test_new_message_event_queues_for_broadcast_correctly()
    {
        Broadcast::shouldReceive('queue')->once()->andReturnUsing(function ($event) {
            $this->assertInstanceOf(NewMessage::class, $event);
            $this->assertEquals('message-box', $event->broadcastOn()->name);
            $this->assertEquals([
                'sender_id' => 1,
                'message' => 'Hello from server!',
            ], $event->broadcastWith());
        });

        event(new NewMessage(1, 'Hello from server!'));

        $this->assertTrue(true);
    }

    public function test_new_message_event_is_dispatched_correctly()
    {
        Event::fake([NewMessage::class]);

        event(new NewMessage(1, 'Unit test message'));

        Event::assertDispatched(NewMessage::class, function ($event) {
            $this->assertEquals('message-box', $event->broadcastOn()->name);
            $this->assertEquals(1, $event->broadcastWith()['sender_id']);
            $this->assertEquals('Unit test message', $event->broadcastWith()['message']);
            $this->assertEquals(['sender_id' => 1, 'message' => 'Unit test message'], $event->broadcastWith());
            return $event->sender_id === 1 &&
                $event->message === 'Unit test message' &&
                $event->broadcastOn()->name === 'message-box';
        });
    }
}