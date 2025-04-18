<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReactionResource;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReactionController extends Controller
{
    public function showReactionSummary()
    {
        try {
            $reaction = Reaction::with('users')->get();

            return response()->json([
                'code' => 200,
                'message' => 'Data reaction summary berhasil diambil',
                'data' => ReactionResource::collection($reaction)
            ]);
        } catch (\Throwable $th) {
            Log::error('ReactionController.showReactionSummary: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }


    public function showUserReaction()
    {
        try {
            $users = User::with('reactions')
                ->filter(request(['search']))
                ->orderByDesc('id')
                ->paginate(8)
                ->withQueryString();
            $allReaction = Reaction::all();

            // $formattedUsers = collect($users->items())->map(function ($user) use ($allReaction) {
            //     return [
            //         'id' => $user->id,
            //         'name' => $user->name,
            //         'reactions' => $allReaction->map(function ($reaction) use ($user) {
            //             return [
            //                 'id' => $reaction->id,
            //                 'name' => $reaction->name,
            //                 'is_selected' => $user->reactions->contains('id', $reaction->id)
            //             ];
            //         })
            //     ];
            // });


            $formattedUsers = collect($users->items())->map(function ($user) use ($allReaction) {
                $formattedReactions = $allReaction->mapWithKeys(function ($reaction) use ($user) {
                    // Konversi nama reaksi ke lowercase dan ubah spasi menjadi underscore
                    $reactionKey = (string) Str::of($reaction->name)->lower()->replace(' ', '_');

                    return [$reactionKey => $user->reactions->contains('id', $reaction->id)];
                });

                return array_merge([
                    'id' => $user->id,
                    'name' => $user->name
                ], $formattedReactions->toArray());
            });

            return response()->json([
                'code' => 200,
                'message' => 'Data reaksi pengguna berhasil diambil',
                'data' => [
                    'current_page' => $users->currentPage(),
                    'first_page_url' => $users->url(1),
                    'from' => $users->firstItem(),
                    'last_page' => $users->lastPage(),
                    'last_page_url' => $users->url($users->lastPage()),
                    'links' => $users->linkCollection(),
                    'next_page_url' => $users->nextPageUrl(),
                    'path' => $users->path(),
                    'per_page' => $users->perPage(),
                    'prev_page_url' => $users->previousPageUrl(),
                    'to' => $users->lastItem(),
                    'total' => $users->total(),
                    'data_user' => $formattedUsers
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('ReactionController.showUserReaction: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function storeReaction(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'reactions_id' => 'required|array',
            'reactions_id.*' => 'required|integer'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validate->errors()->first(),
                'error' => $validate->errors()
            ], 400);
        } else {
            try {
                $data = $validate->validated();

                $user = Auth::guard('user')->user();

                Log::debug("ReactionController.storeReaction: reactions_id -> " . json_encode($data['reactions_id']));

                $user->reactions()->sync($data['reactions_id']);

                return response()->json([
                    'code' => 200,
                    'message' => 'Reaksi berhasil disimpan'
                ]);
            } catch (\Throwable $th) {
                Log::error('ReactionController.storeReaction: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }
}
