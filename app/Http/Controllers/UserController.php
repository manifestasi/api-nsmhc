<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllUserResource;
use App\Http\Resources\DetailUserResource;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Models\UserAppOpen;
use App\Models\UserChild;
use App\Models\UserHusband;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function showUserOpenSummary()
    {
        try {
            $result = DB::table('user_app_opens')
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'ASC')
                ->orderBy('month', 'ASC')
                ->get();

            $totalUserAppOpen = UserAppOpen::count();


            $months = collect(range(1, 12))->map(function ($month) {
                return [
                    'name' => Carbon::createFromFormat('m', $month)->translatedFormat('F'),
                ];
            });


            $years = $result->pluck('year')->unique();

            $visitorData = $months->map(function ($monthRow, $index) use ($years, $result) {
                $row = ['name' => $monthRow['name']];

                foreach ($years as $year) {
                    $data = $result->firstWhere(
                        fn($item) =>
                        $item->month == ($index + 1) && $item->year == $year
                    )?->total ?? 0;

                    $row[$year] = $data;
                }

                return $row;
            })->toArray();

            return response()->json([
                'code' => 200,
                'message' => 'Data user open app summary berhasil diambil',
                'data' => [
                    'total_app_opened' => $totalUserAppOpen,
                    'summary' => $visitorData
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('UserController.showUserSummary: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function trackOpen()
    {
        try {
            $user = Auth::guard('user')->user();
            UserAppOpen::create([
                'users_id' => $user->id
            ]);

            return response()->json([
                'code' => 200,
                'message' => 'Lacak aplikasi dibuka berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            Log::error('UserController.trackOpen: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function updateUser(Request $request, User $user)
    {
        $validate = Validator::make($request->all(), [
            'nama_lengkap_pengguna' => 'required|string',
            'usia_pengguna' => 'required|integer',
            'pendidikan_terakhir_pengguna' => 'required|string',
            'pekerjaan_terakhir_pengguna' => 'required|string',
            'alamat' => 'required|string',
            'no_hp' => 'required|string'
            'nama_lengkap_anak' => 'required|string',
            'usia_anak' => 'required|integer',
            'pendidikan_terakhir_anak' => 'required|string',
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

                $user->update([
                    'name' => $data['nama_lengkap_pengguna']
                ]);

                UserProfile::where('users_id', $user->id)
                    ->first()
                    ->update([
                        'age' => $data['usia_pengguna'],
                        'no_hp' => $data['no_hp'],
                        'last_education' => $data['pendidikan_terakhir_pengguna'],
                        'last_job' => $data['pekerjaan_terakhir_pengguna'],
                        'address' => $data['alamat']
                    ]);

                UserChild::where('users_id', $user->id)
                    ->first()
                    ->update([
                        'users_id' => $user->id,
                        'name' => $data['nama_lengkap_anak'],
                        'age' => $data['usia_anak'],
                        'last_education' => $data['pendidikan_terakhir_anak']
                    ]);

                DB::commit();
                return response()->json([
                    'code' => 200,
                    'message' => 'Data user berhasil diperbarui'
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                Log::error('UserController.updateUser: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }

    public function showDetailUser(User $user)
    {
        try {
            $dataDetailUser = $user->load(['userProfile', 'userChild']);

            return response()->json([
                'code' => 200,
                'message' => 'Data detail pelanggan berhasil diambil',
                'data' => new DetailUserResource($dataDetailUser)
            ]);
        } catch (\Throwable $th) {
            Log::error('UserController.showDetailUser: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function showAllUser()
    {
        try {
            $user = User::with('userProfile', 'userChild')
                ->filter(request(['search']))
                ->orderByDesc('created_at')
                ->paginate(8)
                ->withQueryString();

            return response()->json([
                'code' => 200,
                'message' => 'Data pengguna berhasil diambil',
                'data' => new AllUserResource($user)
            ]);
        } catch (\Throwable $th) {
            Log::error('UserController.showAllUser: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function showHusband()
    {
        try {
            $user = Auth::guard('user')->user();
            $userHusband = UserHusband::where('users_id', $user->id)->first();

            return response()->json([
                'code' => 200,
                'message' => 'Data suami berhasil diambil',
                'data' => $userHusband
            ]);
        } catch (\Throwable $th) {
            Log::error('UserController.showHusband: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function showChildren()
    {
        try {
            $user = Auth::guard('user')->user();
            $userChild = UserChild::where('users_id', $user->id)->first();

            return response()->json([
                'code' => 200,
                'message' => 'Data anak berhasil diambil',
                'data' => $userChild
            ]);
        } catch (\Throwable $th) {
            Log::error('UserController.showChildren: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function showProfile()
    {
        try {
            $user = Auth::guard('user')->user();

            $userProfile = UserProfile::where('users_id', $user->id)->first();

            return response()->json([
                'code' => 200,
                'message' => 'Data diri berhasil diambil',
                'data' => new ProfileResource($userProfile)
            ]);
        } catch (\Throwable $th) {
            Log::error('UserController.showProfile: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function updateChildren(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'anak' => 'required|array',
            'anak.*.nama_lengkap' => 'required|string',
            'anak.*.usia' => 'required|integer',
            'anak.*.pendidikan_terakhir' => 'required|string',
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

                UserChild::where('users_id', $user->id)->delete();

                foreach ($data['anak'] as $a) {
                    UserChild::create([
                        'users_id' => $user->id,
                        'name' => $a['nama_lengkap'],
                        'age' => $a['usia'],
                        'last_education' => $a['pendidikan_terakhir']
                    ]);
                }

                DB::commit();

                return response()->json([
                    'code' => 200,
                    'message' => 'Data anak berhasil diperbarui'
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                Log::error('UserController.updateChildren: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }

    public function updateHusband(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string',
            'usia' => 'required|integer',
            'pendidikan_terakhir' => 'required|string',
            'pekerjaan_terakhir' => 'required|string'
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

                $husband = UserHusband::where('users_id', $user->id)->first();

                $husband->update([
                    'users_id' => $user->id,
                    'name' => $data['nama_lengkap'],
                    'age' => $data['usia'],
                    'last_education' => $data['pendidikan_terakhir'],
                    'last_job' => $data['pekerjaan_terakhir']
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Data suami berhasil diperbarui'
                ]);
            } catch (\Throwable $th) {
                Log::error('UserController.updateHusband: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }

    public function updateProfile(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'usia' => 'required|integer',
            'pendidikan_terakhir' => 'required|string',
            'pekerjaan_terakhir' => 'required|string',
            'alamat' => 'required|string',
            'no_hp' => 'required|string',
            'foto' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
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

                $userProfile = UserProfile::where('users_id', $user->id)->first();

                if ($request->hasFile('foto')) {
                    if ($userProfile->foto !== null) {
                        Storage::delete($userProfile->foto);
                    }

                    $path = $request->file('foto')->store('foto_profile');

                    $userProfile->update([
                        'foto' => $path,
                        'age' => $data['usia'],
                        'no_hp' => $data['no_hp'],
                        'last_education' => $data['pendidikan_terakhir'],
                        'last_job' => $data['pekerjaan_terakhir'],
                        'address' => $data['alamat']
                    ]);
                } else {
                    $userProfile->update([
                        'age' => $data['usia'],
                        'last_education' => $data['pendidikan_terakhir'],
                        'last_job' => $data['pekerjaan_terakhir'],
                        'address' => $data['alamat'],
                        'no_hp' => $data['no_hp']
                    ]);
                }

                return response()->json([
                    'code' => 200,
                    'message' => 'Data diri berhasil di perbarui'
                ]);
            } catch (\Throwable $th) {
                Log::error('UserController.updateProfile: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }
}
