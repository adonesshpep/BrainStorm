<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatars = config('avatars.default');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'overall_score' => $this->overall_score,
            'avatar_url'=>Storage::url($avatars[$this->avatar_id]??$avatars[1])
        ];
    }
}
