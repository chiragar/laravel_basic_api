<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();

        if (count($users) > 0) {
            $response = [
                'message' => 'User Found',
                'status' => 1,
                'data' => $users,
            ];
        } else {
            $response = [
                'message' => count($users) . 'User Not Found',
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }

    public function flagindex($flag)
    {
        $users = User::all();
        // p($users);
        // count($users); //table record count

        $query = User::select('email', 'name');
        if ($flag == 1) {
            $query->where('status', 1);
        } else if ($flag == 0) {
            $query->where('status', 0);
        } else {
            return response()->json(
                [
                    'message' => 'Invalid parameter passed,it can either 1 or 0',
                    'status' => 0,
                ],
                400
            );
        }
        $users = $query->get();
        if (count($users) > 0) {
            $response = [
                'message' => 'User Found',
                'status' => 1,
                'data' => $users,
            ];
        } else {
            $response = [
                'message' => count($users) . 'User Not Found',
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
            'pincode' => ['required', 'min:6'],

        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'pincode' => $request->pincode,
                'password' => Hash::make($request->password)
            ];
            // p($data);
            DB::beginTransaction(); //Transaction Begining
            try {
                $user = User::create($data); //Recored create
                DB::commit(); //success transaction
            } catch (\Exception $e) {
                DB::rollBack(); //transaction rollback
                p($e->getMessage());
            }
            if ($user != null) {
                return response()->json([
                    'message' => 'User Registered successfully'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Internal Server error'
                ], 500);
            }
        }
        // p($request->all());
    }

    public function show($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => 'User not found',
                'status' => 0,
                'data' => []
            ];
        } else {
            $response = [
                'message' => 'User found',
                'status' => 1,
                'data' => $user
            ];
        }
        return response()->json($response, 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => "User doesn't exist",
                'status' => 0
            ];
            $respcode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response = [
                    'message' => "User delete successfully",
                    'status' => 1
                ];
                $respcode = 200;
            } catch (\Throwable $th) {
                DB::rollBack();
                $response = [
                    'message' => "Internal Server error",
                    'status' => 0
                ];
                $respcode = 500;
            }
        }
        return response()->json($response, $respcode);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return response()->json([
                'message' => "User doesn't exist",
                'status' => 0
            ], 404);
        } else {
            DB::beginTransaction();
            try {
                $user->name = $request['name'];
                $user->email = $request['email'];
                $user->contact = $request['contact'];
                $user->pincode = $request['pincode'];
                $user->address = $request['address'];
                $user->save();
                DB::commit();
            } catch (\Throwable $err) {
                DB::rollback();
                $user = null;
            }
            if (is_null($user)) {
                return response()->json([
                    'message' => "Internal Server error",
                    'error_msg' => $err->getMessage(),
                    'status' => 0
                ], 500);
            } else {
                return response()->json([
                    'message' => "Data updated successfully",
                    'status' => 1
                ], 200);
            }
        }
    }

    public function changePassword(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json([
                'message' => "User doesn't exist",
                'status' => 0
            ], 404);
        } else {
            if (Hash::check($request['old_password'],$user->password)) {
                if ($request['new_password']==$request['confirm_password']) {
                    DB::beginTransaction();
                    try {
                        $user->password = Hash::make($request['new_password']);
                        $user->save();
                        DB::commit();
                    } catch (\Throwable $err) {
                        DB::rollback();
                        $user = null;
                    }
                    if (is_null($user)) {
                        return response()->json([
                            'message' => "Internal Server error",
                            'error_msg' => $err->getMessage(),
                            'status' => 0
                        ], 500);
                    } else {
                        return response()->json([
                            'message' => "Password updated successfully",
                            'status' => 1
                        ], 200);
                    }
                }else{
                    return response()->json([
                        'message' => "New password and confirm password doesn't match",
                        'status' => 0
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => "Old password doesn't match",
                    'status' => 0
                ], 400);
            }
        }
    }
}
