<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function getDataGrafikQuestionAnswer()
    {
        try {
            $question = DB::table('questions')
                ->join('answers', 'questions.id', '=', 'answers.questions_id')
                ->select(
                    'questions.question_text as question',
                    DB::raw("JSON_ARRAYAGG(JSON_OBJECT(
                        'id', answers.id,
                        'answer_text', answers.answer_text
                    )) as answers")
                )
                ->groupBy('questions.question_text')
                ->get()
                ->map(function ($q) {
                    return (object) [
                        'question' => $q->question, // Pastikan ini bisa diakses sebagai properti
                        'answers' => json_decode($q->answers, true) // Decode JSON sebagai array
                    ];
                });

            return response()->json([
                'code' => 200,
                'message' => 'Ambil data grafik Pertanyaan dan jawaban berhasil',
                'data' => QuestionResource::collection($question)
            ]);
        } catch (\Throwable $th) {
            Log::error('QuestionController.getDataGrafikQuestionAnswer: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function storeQuestionAnswer(Request $request)
    {
        $validate = Validator::make($request->all(), [
            '*.question' => 'required|string',
            '*.answer' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validate->errors()->first(),
                'error' => $validate->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $data = $validate->validated();
                $user = Auth::guard('user')->user();

                if (empty($data)) {
                    return response()->json([
                        'code' => 400,
                        'message' => 'Jawaban tidak boleh kosong'
                    ], 400);
                }

                foreach ($data as $d) {
                    $question = Question::create([
                        'question_text' => $d['question']
                    ]);

                    Answer::create([
                        'questions_id' => $question->id,
                        'users_id' => $user->id,
                        'answer_text' => $d['answer']
                    ]);
                }

                DB::commit();
                return response()->json([
                    'code' => 200,
                    'message' => 'Jawaban berhasil disimpan'
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                Log::error('QuestionController.storeQuestionAnswer: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }
}
