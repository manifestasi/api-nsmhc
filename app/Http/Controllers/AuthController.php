<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\UserChild;
use App\Models\UserHusband;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function updateDataSecurityUser(Request $request, User $user)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
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
                $user->update([
                    'email' => $data['email'],
                    'password' => Hash::make($data['password'])
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Data keamanan berhasil di perbarui'
                ]);
            } catch (\Throwable $th) {
                Log::error('AuthController.updateDataSecurityUser: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }

    public function showDataSecurityUser(User $user)
    {
        try {
            return response()->json([
                'code' => 200,
                'message' => 'Data keamanan user berhasil diambil',
                'data' => [
                    'nama' => $user->name,
                    'email' => $user->email
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('AuthController.loginUser: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function loginAdmin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
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

                $admin = Admin::where('email', $data['email'])->first();

                if (!$admin || !Hash::check($data['password'], $admin->password)) {
                    return response()->json([
                        'code' => 401,
                        'message' => 'Email or password wrong',
                    ], 401);
                }

                $token = $admin->createToken($admin->email, ['admin']);

                return response()->json([
                    'code' => 200,
                    'message' => 'Login Berhasil',
                    'token' => $token->plainTextToken
                ]);
            } catch (\Throwable $th) {
                Log::error('AuthController.loginUser: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }

    public function logoutAdmin()
    {
        try {
            Auth::guard('admin')->user()->currentAccessToken()->delete();
            return response()->json([
                'code' => '200',
                'message' => 'Logout berhasil'
            ]);
        } catch (\Throwable $th) {
            Log::error('AuthController.logoutAdmin: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function logoutUser()
    {
        try {
            Auth::guard('user')->user()->currentAccessToken()->delete();

            return response()->json([
                'code' => '200',
                'message' => 'Logout berhasil'
            ]);
        } catch (\Throwable $th) {
            Log::error('AuthController.logoutUser: ' . $th->getMessage());
            return response()->json([
                'code' => 500,
                'message' => "Something wrong",
            ], 500);
        }
    }

    public function registerUser(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'nama_lengkap_pengguna' => 'required|string',
            'usia_pengguna' => 'required|integer',
            'pendidikan_terakhir_pengguna' => 'required|string',
            'pekerjaan_terakhir_pengguna' => 'required|string',
            'alamat' => 'required|string',
            'no_hp' => 'required|string',
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

                // Buat User
                $user = User::create([
                    'email' => $data['email'],
                    'name' => $data['nama_lengkap_pengguna'],
                    'password' => $data['password'],
                    'email_verified_at' => now()
                ]);

                // Buat Profile Pengguna
                UserProfile::create([
                    'users_id' => $user->id,
                    'age' => $data['usia_pengguna'],
                    'no_hp' => $data['no_hp'],
                    'last_education' => $data['pendidikan_terakhir_pengguna'],
                    'last_job' => $data['pekerjaan_terakhir_pengguna'],
                    'address' => $data['alamat']
                ]);

                // Buat Data Suami
                // UserHusband::create([
                //     'users_id' => $user->id,
                //     "name" => $data['nama_lengkap_suami'],
                //     'age' => $data['usia_suami'],
                //     'last_education' => $data['pendidikan_terakhir_suami'],
                //     'last_job' => $data['pekerjaan_terakhir_suami']
                // ]);

                // Buat Data Anak
                UserChild::create([
                    'users_id' => $user->id,
                    'name' => $data['nama_lengkap_anak'],
                    'age' => $data['usia_anak'],
                    'last_education' => $data['pendidikan_terakhir_anak']
                ]);

                DB::commit();

                return response()->json([
                    'code' => 201,
                    'message' => 'Register berhasil'
                ], 201);
            } catch (\Throwable $th) {
                DB::rollback();
                Log::error('AuthController.registerUser: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }

    public function loginUser(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
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

                $user = User::where('email', $data['email'])->first();

                if (!$user || !Hash::check($data['password'], $user->password)) {
                    return response()->json([
                        'code' => 401,
                        'message' => 'Email or password wrong',
                    ], 401);
                }

                $token = $user->createToken($user->email, ['user']);

                return response()->json([
                    'code' => 200,
                    'message' => 'Login Berhasil',
                    'token' => $token->plainTextToken
                ]);
            } catch (\Throwable $th) {
                Log::error('AuthController.loginUser: ' . $th->getMessage());
                return response()->json([
                    'code' => 500,
                    'message' => "Something wrong",
                ], 500);
            }
        }
    }
}
