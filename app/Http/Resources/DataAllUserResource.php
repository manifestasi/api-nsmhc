<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataAllUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'foto' => $this->userProfile->foto ? request()->getSchemeAndHttpHost() . '/storage/' . $this->userProfile->foto : null,
            'name' => $this->name,
            'usia' => $this->userProfile->age,
            'last_job' => $this->userProfile->last_job,
            'no_hp' => $this->userProfile->no_hp
        ];
    }
}
