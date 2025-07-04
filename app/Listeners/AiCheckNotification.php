<?php

namespace App\Listeners;

use App\Events\NewPuzzleCreated;
use App\Providers\DeepSeekService;
use App\Models\Puzzle;
use App\Models\Solution;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AiCheckNotification
{
    /**
     * Create the event listener.
     */
    public $deepseek;
    public function __construct(DeepSeekService $deepseek)
    {
        $this->deepseek = $deepseek;
    }

    /**
     * Handle the event.
     * 
     * @return void
     */
    public function handle($event): void
    {
        $deepseek = $this->deepseek;
        $puzzle = Puzzle::find($event->puzzle_id);
        $question = $puzzle->question;
        $solutions = $event->solutions;
        $aiAnswer=1;
        $solutions = collect($solutions)->filter(fn($solution) => $solution->iscorrect)->values();
foreach($solutions as $answer){
        $messages = [
            ['role' => 'user', 'content' => "You are an AI designed to check the correctness of answers to puzzles. 
Here is the puzzle and the user provided answer:

Puzzle:" . $question . "
User's Answer: " . $answer->value . "

Respond only with:
-  1  if the answer is correct.
-  0  if the answer is incorrect.

Respond **only** with `1` if the answer is correct or `0` if the answer is incorrect. Do not add any extra text or explanations and dont send json only 1 or 0."]
        ];
        Log::error($messages);
        $response = $deepseek->chatCompletion($messages);
        $content = ($response['choices'][0]['message']['content'] ?? 0);
        Log::error($content);
        if((int)$content===0){
            $aiAnswer=0;
        }
        }
        if($aiAnswer){
            $puzzle->status=1;
            $puzzle->save();
        }
    }
}
