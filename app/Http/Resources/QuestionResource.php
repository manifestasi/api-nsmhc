<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'question' => $this->question,
            'answers' => $this->countDuplicate($this->answers, 'answer_text')
        ];
    }

    private function countDuplicate($array, $key)
    {
        // $hasil = [];

        // foreach ($array as $item) {
        //     if (isset($item[$key])) {
        //         $hasil[$item[$key]] = ($hasil[$item[$key]] ?? 0) + 1;
        //     }
        // }

        // return $hasil;

        $hasil = [];

        foreach ($array as $item) {
            if (isset($item[$key])) {
                $hasil[$item[$key]] = ($hasil[$item[$key]] ?? 0) + 1;
            }
        }

        $formattedResult = [];
        foreach ($hasil as $answer_text => $count) {
            $formattedResult[] = [
                'answer_text' => $answer_text,
                'count' => $count
            ];
        }

        return $formattedResult;
    }
}
