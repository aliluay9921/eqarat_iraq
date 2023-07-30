<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Image;
use App\Traits\Filter;
use App\Traits\Search;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HotelController extends Controller
{
    use SendResponse, Pagination, Search, Filter, OrderBy;

    public function getHotels()
    {
        $hotels = Hotel::select("*");

        if (isset($_GET["query"])) {
            $hotels->where(function ($q) {
                $q->whereHas("user", function ($query) {
                    $query->where("user_name", 'LIKE', '%' . $_GET['query'] . '%');
                });
                $columns = Schema::getColumnListing('hotels');
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
                }
            });
            // $hotels = $this->search($hotels, 'hotels');
        }
        if (isset($_GET['filter'])) {
            $this->filter($hotels, $_GET["filter"]);
        }
        if (isset($_GET)) {
            $this->order_by($hotels, $_GET);
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($hotels->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب الفنادق بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addHotel(Request $request)
    {
        $validator = Validator($request->all(), [
            "desc" => "required|string",
            'room_type' => 'required',
            'images' => "requires",
        ], [
            'desc.required' => "يجب أدخال الوصف",
            'room_type.required' => "يجب أدخال نوع الغرفة",
            "images.required" => "يجب اضافة صورة واحدة على الاقل"

        ]);
        if ($validator->fails()) {
            return $this->send_response(422, 'خطأ في البيانات', $validator->errors(), null, null, 0);
        }
        $hotel = Hotel::create([
            "desc" => $request->desc,
            "room_type" => $request->room_type,
            "note" => $request->note ?? null,
            "user_id" => auth()->user()->id,
        ]);
        foreach ($request->images as $image) {
            Image::create([
                "target_id" => $hotel->id,
                "image" => $this->uploadPicture($image, '/images/hotel/'),
            ]);
        }
        return $this->send_response(200, 'تم اضافة الغرفة بنجاح', [], $hotel);
    }

    public function updateHotel(Request $request)
    {
        $validator = Validator($request->all(), [
            "desc" => "required|string",
            'room_type' => 'required',
            'hotel_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->send_response(422, 'خطأ في البيانات', $validator->errors(), null, null, 0);
        }
        $hotel = Hotel::find($request->hotel_id);
        $hotel->update([
            "desc" => $request->desc,
            "room_type" => $request->room_type,
            "note" => $request->note ?? null,
        ]);
        if ($request->images) {
            foreach ($request->images as $image) {
                Image::create([
                    "target_id" => $hotel->id,
                    "image" => $this->uploadPicture($image, '/images/hotel/'),
                ]);
            }
        }
        return $this->send_response(200, 'تم تعديل الغرفة بنجاح', [], $hotel);
    }

    public function deleteHotel(Request $request)
    {
        $validator = Validator($request->all(), [
            'hotel_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->send_response(422, 'خطأ في البيانات', $validator->errors(), null, null, 0);
        }
        Hotel::find($request->hotel_id)->delete();
        return $this->send_response(200, 'تم حذف الغرفة بنجاح', [], []);
    }
}
