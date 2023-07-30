<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Filter;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\Search;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use SendResponse, Pagination, UploadImage, Search, Filter, OrderBy;

    public function getAllUsers(Request $request)
    {
        $users = User::select("*");
        if (isset($_GET["query"])) {
            $this->search($users, 'users');
        }
        if (isset($_GET['filter'])) {
            $this->filter($users, $_GET["filter"]);
        }
        if (isset($_GET)) {
            $this->order_by($users, $_GET);
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($users->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب المستخدمين بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function getUser()
    {
        $users = User::select("*");
        if (isset($_GET["query"])) {
            $this->search($users, 'users');
        }
        if (isset($_GET['filter'])) {
            $this->filter($users, $_GET["filter"]);
        }
        if (isset($_GET)) {
            $this->order_by($users, $_GET);
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($users->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب المستخدمين بنجاح', [], $res["model"], null, $res["count"]);
    }



    public function login(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'phone_number' => 'required',
            'password' => 'required'
        ], [
            'phone_number.required' => 'يرجى ادخال رقم الهاتف',
            'password.required' => 'يرجى ادخال كلمة المرور ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $fieldType = filter_var(is_numeric($request["phone_number"])) ? 'phone_number' : 'user_name';

        if (auth()->attempt(array($fieldType => $request['phone_number'], 'password' => $request['password']))) {
            // $user = Auth::user();
            $user = auth()->user();
            $token = $user->createToken('eaqarat-iraq-ali-luay')->accessToken;
            return $this->send_response(200, 'تم تسجيل الدخول بنجاح', [], $user, $token);
        } else {
            return $this->send_response(400, 'هناك مشكلة تحقق من تطابق المدخلات', null, null, null);
        }
    }


    public function register(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'phone_number' => 'required|unique:users,phone_number',
            'user_name' => 'required|unique:users,user_name',
            'password' => 'required',
            'user_type' => 'required',
        ], [
            'phone_number.required' => 'يرجى ادخال رقم الهاتف',
            'user_name.required' => 'يرجى ادخال اسم المستخدم ',
            'phone_number.unique' => 'رقم الهاتف الذي قمت بأدخاله تم استخدامه سابقاً',
            'user_name.unique' => 'اسم المستخدم الذي قمت بأدخاله تم استخدامه سابقاً',
            'password.required' => 'يرجى ادخال كلمة المرور ',
            'user_type.required' => 'يرجى ادخال نوع المستخدم ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data = [
            'user_name' => $request['user_name'],
            'phone_number' => $request['phone_number'],
            'password' => bcrypt($request['password']),
            'user_type' => $request["user_type"],
            'address' => $request["address"] ?? null,
            'longetude' => $request["longetude"] ?? null,
            'latetude' => $request["latetude"] ?? null,

        ];
        if (array_key_exists('image', $request)) {
            $data['image'] = $this->uploadPicture($request['image'], '/images/user_images/');
        }
        // return $data;
        $user = User::create($data);
        $token = $user->createToken($user->user_name)->accessToken;

        return $this->send_response(200, 'تم تسجيل الدخول بنجاح', [], User::find($user->id), $token);
    }

    public function updateProfile(Request $request)
    {
        $request = $request->json()->all();

        $validator = Validator::make($request, [
            'user_name' => 'required|unique:users,user_name,' . auth()->user()->id,
            'phone_number' => 'required|unique:users,phone_number,' . auth()->user()->id,
            'user_type' => 'required',
        ], [
            'phone_number.required' => 'يرجى ادخال رقم الهاتف',
            'user_name.required' => 'يرجى ادخال اسم المستخدم ',
            'phone_number.unique' => 'رقم الهاتف الذي قمت بأدخاله تم استخدامه سابقاً',
            'user_name.unique' => 'اسم المستخدم الذي قمت بأدخاله تم استخدامه سابقاً',
            'user_type.required' => 'يرجى ادخال نوع المستخدم ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data = [
            'user_name' => $request['user_name'] ?? auth()->user()->user_name,
            'phone_number' => $request['phone_number'] ?? auth()->user()->phone_number,
            'user_type' => $request["user_type"] ?? auth()->user()->user_type,
            'adderss' => $request["adderss"] ?? null,
            'longetude' => $request["longetude"] ?? null,
            'latetude' => $request["latetude"] ?? null,

        ];
        if (array_key_exists('image', $request)) {
            $data['image'] = $this->uploadPicture($request['image'], '/images/user_images/');
        }
        // return $data;
        $user = User::find(auth()->user()->id)->update($data);
        return $this->send_response(200, 'تم تعديل البيانات بنجاح', [], User::find(auth()->user()->id));
    }

    public function changeActiveUser(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:users,id',
        ], [
            'id.required' => 'يرجى ادخال رقم المستخدم',
            'id.exists' => 'رقم المستخدم غير موجود',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $user = User::find($request['id']);
        if ($user) {
            $user->update(['active' => !$user->active]);
            return $this->send_response(200, 'تم تغير حالة المستخدم', [], User::find($request['id']));
        } else {
            return $this->send_response(400, 'هناك مشكلة تحقق من تطابق المدخلات', null, null, null);
        }
    }
}