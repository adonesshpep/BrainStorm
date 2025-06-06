<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CommunityResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatars=config('categoryavatars.default');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar_url'=>Storage::url(($avatars[$this->avatar_id])??$avatars[1])
        ];
    }
}
