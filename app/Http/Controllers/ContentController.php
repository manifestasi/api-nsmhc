<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource2;
use App\Models\Content;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function countUserCompletedProgress()
    {
        try {
            $user = User::with('contents')->get();

            $content = Content::count();

            $sum = 0;

            foreach ($user as $u) {
                $isCompleted = $u->contents->count() == $content;
                if ($isCompleted) {
                    $sum++;
                }
            }
            return response()->json([
                'code' => 200,
                'message' => 'Hitung pengguna yang menyelesaikan content berhasil',
                'data' => [
                    'total_user' => $user->count(),
                    'user_completed_progress' => $sum
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('ContentController.countUserCompletedProgress: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }
    public function showProgress()
    {
        try {
            $users = User::with('contents')
                ->filter(request(['search']))
                ->orderByDesc('created_at')
                ->paginate(8)
                ->withQueryString();
            $allContent = Content::all();

            // $formattedUsers = collect($users->items())->map(function ($user) use ($allContent) {
            //     return [
            //         'id' => $user->id,
            //         'name' => $user->name,
            //         'contents' => $allContent->map(function ($content) use ($user) {
            //             return [
            //                 'id' => $content->id,
            //                 'name' => $content->name,
            //                 'is_completed' => $user->contents->contains('id', $content->id)
            //             ];
            //         })
            //     ];
            // });

            $formattedUsers = collect($users->items())->map(function ($user) use ($allContent) {
                $formattedContents = $allContent->mapWithKeys(function ($content) use ($user) {
                    // Konversi nama reaksi ke lowercase dan ubah spasi menjadi underscore
                    $contentKey = (string) Str::of($content->name)->lower()->replace(' ', '_');

                    return [$contentKey => $user->contents->contains('id', $content->id)];
                });

                return array_merge([
                    'id' => $user->id,
                    'name' => $user->name
                ], $formattedContents->toArray());
            });


            return response()->json([
                'code' => 200,
                'message' => 'Data progress content user berhasil diambil',
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
            Log::error('ContentController.showProgress: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function storeProgress(Content $content)
    {
        try {
            $user = Auth::guard('user')->user();
            $alreadyExists = $content->users()->where('users.id', $user->id)->count();
            if (!$alreadyExists) {
                $content->users()->sync($user->id);
            }

            Log::debug('ContentController.storeProgress: alreadyExist? ' . $alreadyExists);

            return response()->json([
                'code' => 200,
                'message' => 'Progres ' . $content->name . ' berhasil diperbarui'
            ]);
        } catch (\Throwable $th) {
            Log::error('ContentController.storeProgress: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }
}
