<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
    *@group UserManagement
    *@response status=200 scenario="success" {
    * "code": int ,
    * "success": boolean,
    * "message": string,
    * "result": [
    *    {
    *        "id": int,
    *        "first_name": string,
    *        "last_name": string,
    *        "middle_name": string,
    *        "username": string,
    *        "email": string,
    *        "created_at": string,
    *        "updated_at": string,
    *        "attachments": [
    *           {
    *            "id": int,
    *            "path": string,
    *            "type": string,
    *            "model_name": string,
    *            "target_id": int,
    *            "created_at": string,
    *            "updated_at": string
    *           }
    *       ]
    *    }
    *   ]
    *}
    **/
    public function index()
    {
        $response = $this->response(result:User::with('attachments')->get());
        return $response;
    }
    /**
    *@group UserManagement
    *@bodyParam first_name string required The first name of the user. Example: John
    *@bodyParam middle_name string required The middle name of the user. Example: Doe
    *@bodyParam last_name string required The last name of the user. Example: Smith
    *@bodyParam username string required The username of the user. Example: jsmith
    *@bodyParam email string required The email of the user. Example: smith@gmail.com
    *@bodyParam password string required The password of the user. Example: 12345678
    *@bodyParam attachments array required The image of the user that will be trained base64.
    *@bodyParam attachments.* string required The image of the user that will be trained base64.

    *@response status=200 scenario="success" {
    *        "first_name":string,
    *        "middle_name":string,
    *        "last_name":string,
    *        "email":string,
    *        "username":string,
    *        "password":string,
    *        "attachments":[] //base64
    *    }
    **/
    public function create_new_user(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name'=> 'required|string',
            'middle_name'=> 'required|string',
            'last_name'=>'required|string',
            'email'=> 'required|unique:users,email',
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'attachments'=>'required|array',
            'attachments.*'=>'string',
        ]);

        if ($validator->fails()) {
            $response = $this->response(
                code:422,
                message:"هناك خطأ بالمدخلات",
                error:$validator->errors()->all(),
            );
            return $response;
        } else {
            $data = $request->only([
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'username',
                'password',
            ]);
            $data['password'] = bcrypt($data['password']);
            $user = User::create($data);
            //attachment
            $attachment_path = [];
            if($request->attachments) {
                foreach ($request->attachments as $base64) {
                    $path =  $this->uploadPicture($base64, '/attachments/training/'.$user->id.'/');
                    Attachment::create([
                        'type'=>'training',
                        'path'=>$path,
                        'model_name'=>'user',
                        'target_id'=>$user->id,
                    ]);
                    $attachment_path[] = $path;
                    //send to hussien
                }
            }

            return $this->response(
                code:200,
                message:"تم اضافه مستخدم جديد",
                result:$user
            );

        }

    }

    /**
    * @group Auth
    * @bodyParam username string required The username of the user. Example: admin
    * @bodyParam password string required The password of the user. Example: admin
    * @response status=200 scenario="success" {
    *"code": int,
    *"success": boolean,
    *"message": string,
    *"result": [
    *    {
    *        "id": int,
    *        "first_name": string,
    *        "last_name": string,
    *        "middle_name": string,
    *        "username": string,
    *        "email": string,
    *        "created_at": string,
    *        "updated_at": string,
    *        "attachments": array
    *    }
    *],
    *"token": string
    *}
    * @response status=422 scenario="when validation fails" {
    * "code": int,
    * "success": boolean,
    * "message": string,
    * "errors": array
    * }
    * @response status=401 scenario="when invalid credentials" {
    * "code": int,
    * "success": boolean,
    * "message": string,
    * }
    **/
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $validator = Validator::make($credentials, [
            'username' => 'required|exists:users,username',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response = $this->response(
                code:422,
                message:"هناك خطأ بالمدخلات",
                error:$validator->errors()->all(),
            );
            return $response;
        }

        $isValidCredentials = Auth::guard('web')->attempt($credentials);
        if (!$isValidCredentials) {
            $response = $this->response(
                code:401,
                message:"البريد الإلكتروني أو كلمة المرور غير صحيحة",
            );
            return $response;
        } else {
            $user = User::with('attachments')->find(Auth::guard('web')->user()->id);
            $token  =  $user->createToken('token')->accessToken;
            return $this->response(result: $user, token:$token);
        }
    }

    /**
     * @group UserManagement
    *@queryParam id required The id of the user. Example: 1
    *@bodyParam first_name string required The first name of the user. Example: John
    *@bodyParam middle_name string required The middle name of the user. Example: Doe
    *@bodyParam last_name string required The last name of the user. Example: Smith
    *@bodyParam username string required The username of the user. Example: user_smith
    *@bodyParam email string required The email of the user. Example: ibra@gmail.com
    *@bodyParam password string The new password of the user. Example: 12345678
    *@bodyParam attachments array required The id of image to be sync.
    *@bodyParam attachments.* int required The id of image to be sync.
    **/
    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'id'=> 'required|exists:users,id',
            'first_name'=> 'required|string',
            'middle_name'=> 'required|string',
            'last_name'=>'required|string',
            'email'=> 'required|unique:users,email,'.$request->id,
            'username' => 'required|unique:users,username'.$request->id,
            'password' => 'nullable|string',
            'attachments'=>'required|array',
            'attachments.*'=>'int',
            'new_attachments'=>'required|array',
            'new_attachments.*'=>'string'
            ]
        );
        if($validator->fails()) {
            $response = $this->response(
                code:422,
                message:"هناك خطأ بالمدخلات",
                error:$validator->errors()->all(),
            );
            return $response;
        }
        $user = User::find($request->id);
        $data = $request->only([
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'username',
            'password',
        ]);
        if($data['password']) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);
        //attachment
        if($request->attachments) {
            $user->attachments()->whereNotIn('id', $request->attachments)->delete();
        }
        $attachment_path = [];
        if($request->new_attachments) {
            foreach ($request->new_attachments as $base64) {
                $path =  $this->uploadPicture($base64, '/attachments/training/'.$user->id.'/');
                Attachment::create([
                    'type'=>'training',
                    'path'=>$path,
                    'model_name'=>'user',
                    'target_id'=>$user->id,
                ]);
                $attachment_path[] = $path;
                //send to hussien
            }
        }
    }
}
