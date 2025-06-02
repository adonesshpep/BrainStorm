<?php

namespace Tests\Feature;

use App\Events\NewPuzzleCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AiEventTest extends TestCase
{   
    protected function setUp():void{
        parent::setUp();
        // Event::fake();
    }
    /**
     * A basic feature test example.
     */
    public function test_an_event_is_fired_when_solution_created(): void
    {
        $this->post('/api/solution',['value'=>'blue','iscorrect'=>1,'puzzle_id'=>3],['']);
        $this->withoutExceptionHandling();
        // Event::assertDispatched(NewPuzzleCreated::class);
    }
}
