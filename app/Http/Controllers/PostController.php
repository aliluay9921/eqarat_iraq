<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Post;
use App\Traits\Filter;
use App\Traits\Search;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use SendResponse, Pagination, UploadImage, Search, OrderBy, Filter;
    public function getPosts()
    {
        if (isset($_GET["post_id"])) {
            $post = Post::with("images")->find($_GET["post_id"]);
            return $this->send_response(200, 'تم جلب العقار بنجاح', [], $post, null, 1);
        }
        $posts = Post::with("images");
        if (isset($_GET["query"])) {
            $posts = $this->search($posts, 'posts');
        }
        // [{"name":"address","value":"baghdad"},
        // {"name":"item_type","value":"1"},
        // {"name":"price","value":[500000,900000]}]
        if (isset($_GET['filter'])) {
            $filter = json_decode($_GET['filter']);
            // return $filter;
            foreach ($filter as $_filter) {
                if ($_filter->name === 'price') {
                    $posts->whereBetween('price', [$_filter->value[0], $_filter->value[1]]);
                } else {
                    $posts->Where($_filter->name, $_filter->value);
                }
            }
        }
        if (isset($_GET)) {
            $this->order_by($posts, $_GET);
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($posts->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب العقارات بنجاح', [], $res["model"], null, $res["count"]);
    }
    public function addPost(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator(
            $request,
            [
                "phone_number" => "required",
                "address" => "required|string",
                "images" => "required",
                'longetude' => 'required',
                'latetude' => 'required',
                'item_type' => "required",
                'item_status' => "required",
                'price' => "required",

            ],
            [
                "phone_number.required" => "رقم الهاتف مطلوب",
                "address.required" => "العنوان مطلوب",
                "images.required" => "الصور مطلوبة",
                "longetude.required" => "خط الطول مطلوب",
                "latetude.required" => "خط العرض مطلوب",
                "item_type.required" => "نوع العقار مطلوب",
                "item_status.required" => "حالة العقار مطلوبة",
                "price.required" => "السعر مطلوب",
            ]
        );
        if ($validator->fails()) {
            return $this->send_response(422, 'خطأ في البيانات', $validator->errors(), null, null, 0);
        }
        $post = Post::create([
            "user_id" => auth()->user()->id,
            "phone_number" => $request["phone_number"],
            "address" => $request["address"],
            "longetude" => $request["longetude"],
            "latetude" => $request["latetude"],
            "item_type" => $request["item_type"],
            "item_status" => $request["item_status"],
            "price" => $request["price"],
            "desc" => $request["desc"] ?? null,
            "note" => $request["note"] ?? null,
        ]);
        if (isset($request["images"])) {
            foreach ($request["images"] as $image) {
                Image::create([
                    "target_id" => $post->id,
                    "image" => $this->uploadPicture($image, '/images/posts/'),
                ]);
            }
        }
        return $this->send_response(200, 'تم اضافة المنشور بنجاح', [], $post);
    }

    public function updatePost(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator(
            $request,
            [
                "post_id" => "required",
                "phone_number" => "required",
                "address" => "required|string",
                "images" => "required",
                'longetude' => 'required',
                'latetude' => 'required',
                'item_type' => "required",
                'item_status' => "required",
                'price' => "required",

            ],
            [
                "post_id.required" => "رقم العقار مطلوب",
                "phone_number.required" => "رقم الهاتف مطلوب",
                "address.required" => "العنوان مطلوب",
                "images.required" => "الصور مطلوبة",
                "longetude.required" => "خط الطول مطلوب",
                "latetude.required" => "خط العرض مطلوب",
                "item_type.required" => "نوع العقار مطلوب",
                "item_status.required" => "حالة العقار مطلوبة",
                "price.required" => "السعر مطلوب",
            ]
        );
        if ($validator->fails()) {
            return $this->send_response(422, 'خطأ في البيانات', $validator->errors(), null, null, 0);
        }
        $post = Post::find($request["post_id"]);
        if (!$post) {
            return $this->send_response(422, 'خطأ في البيانات', ["post_id" => "العقار غير موجود"], null, null, 0);
        }
        $post->update([
            "phone_number" => $request["phone_number"],
            "address" => $request["address"],
            "longetude" => $request["longetude"],
            "latetude" => $request["latetude"],
            "item_type" => $request["item_type"],
            "item_status" => $request["item_status"],
            "price" => $request["price"],
            "desc" => $request["desc"] ?? null,
            "note" => $request["note"] ?? null,
        ]);
        if (isset($request["images"])) {
            foreach ($request["images"] as $image) {
                Image::create([
                    "target_id" => $post->id,
                    "image" => $this->uploadPicture($image, '/images/posts/'),
                ]);
            }
        }
        return $this->send_response(200, 'تم تعديل المنشور بنجاح', [], $post);
    }

    public function deletePost(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator(
            $request,
            [
                "post_id" => "required",
            ],
            [
                "post_id.required" => "رقم العقار مطلوب",
            ]
        );
        if ($validator->fails()) {
            return $this->send_response(422, 'خطأ في البيانات', $validator->errors(), null, null, 0);
        }
        $post = Post::find($request["post_id"]);
        if (!$post) {
            return $this->send_response(422, 'خطأ في البيانات', ["post_id" => "العقار غير موجود"], null, null, 0);
        }
        $post->delete();
        return $this->send_response(200, 'تم حذف المنشور بنجاح', [], $post);
    }
}
