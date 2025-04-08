<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->userProfile->id,
            'users_id' => $this->userProfile->users_id,
            'name' => $this->name,
            'email' => $this->email,
            'foto' => $this->userProfile->foto ? request()->getSchemeAndHttpHost() . '/storage/' . $this->userProfile->foto : null,
            'age' => $this->userProfile->age,
            'no_hp' => $this->userProfile->no_hp,
            'last_education' => $this->userProfile->last_education,
            'last_job' => $this->userProfile->last_job,
            'address' => $this->userProfile->address
        ];
    }
}
