<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'user_id'=>$this->user_id,
            'puzzle_id'=>$this->puzzle_id,
            'solution_id'=>$this->solution_id,
            'answer'=>$this->answer,
            'iscorrect'=>$this->iscorrect
        ];
    }
}
