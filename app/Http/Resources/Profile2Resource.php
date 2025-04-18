<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Profile2Resource extends JsonResource
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
            'users_id' => $this->users_id,
            'foto' => $this->foto ? request()->getSchemeAndHttpHost() . '/storage/' . $this->foto : null,
            'age' => $this->age,
            'no_hp' => $this->no_hp,
            'last_education' => $this->last_education,
            'last_job' => $this->last_job,
            'address' => $this->address
        ];
    }
}
