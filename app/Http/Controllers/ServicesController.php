<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Service;
use App\Traits\Filter;
use App\Traits\Search;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
    use SendResponse, Pagination, UploadImage, Filter, Search, OrderBy;

    public function getServices()
    {
        if (isset($_GET["service_id"])) {
            $service = Service::with("images")->find($_GET["service_id"]);
            return $this->send_response(200, 'تم جلب الخدمة بنجاح', [], $service, null, 1);
        }
        $services = Service::with("images");

        if (isset($_GET["query"])) {
            $services = $this->search($services, 'services');
        }
        if (isset($_GET['filter'])) {
            $this->filter($services, $_GET["filter"]);
        }
        if (isset($_GET)) {
            $this->order_by($services, $_GET);
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($services->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب الخدمات بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function getAuthService()
    {
        $services = Service::with("images")->where("user_id", auth()->user()->id);

        if (isset($_GET["query"])) {
            $services = $this->search($services, 'services');
        }
        if (isset($_GET['filter'])) {
            $this->filter($services, $_GET["filter"]);
        }
        if (isset($_GET)) {
            $this->order_by($services, $_GET);
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($services->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب الخدمات بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addService(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "desc" => 'required',
            "images" => "required",
            "time_to_finish" => 'required',
            "address_project" => "required"
        ], [
            "desc.required" => " يجب أدخال الوصف ",
            "images.required" => " يجب أدخال الصور ",
            "time_to_finish.required" => " يجب أدخال مدة التنفيذ ",
            "address_project.required" => " يجب أدخال عنوان المشروع "
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data["desc"] = $request["desc"];
        $data["offer"] = $request["offer"] ?? null;
        $data["expaired_offer"] = $request["expaired_offer"] ?? null;
        $data["time_to_finish"] = $request["time_to_finish"] ?? null;
        $data["address_project"] = $request["address_project"] ?? null;


        $data["user_id"] = auth()->user()->id;
        $service = Service::create($data);
        foreach ($request["images"] as $image) {
            Image::create([
                "target_id" => $service->id,
                "image" => $this->uploadPicture($image, '/images/services/'),
            ]);
        }
        return $this->send_response(200, 'تم اضافة الخدمة بنجاح', [], Service::find($service->id));
    }


    public function editService(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "service_id" => 'required',
            "desc" => 'required',
            "images" => "required"
        ], [
            "service_id.required" => " يجب أدخال رقم الخدمة ",
            "desc.required" => " يجب أدخال الوصف ",
            "images.required" => " يجب أدخال الصور ",
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $service = Service::find($request["service_id"]);
        if (!$service) {
            return $this->send_response(400, "حصل خطأ في المدخلات", ["service_id" => "رقم الخدمة غير صحيح"], []);
        }
        $data = [];
        $data["desc"] = $request["desc"];
        $data["offer"] = $request["offer"] ?? null;
        $data["expaired_offer"] = $request["expaired_offer"] ?? null;
        $data["time_to_finish"] = $request["time_to_finish"] ?? null;
        $data["address_project"] = $request["address_project"] ?? null;

        $data["user_id"] = auth()->user()->id;
        $service->update($data);
        foreach ($request["images"] as $image) {
            Image::create([
                "target_id" => $service->id,
                "image" => $this->uploadPicture($image, '/images/services/'),
            ]);
        }
        return $this->send_response(200, 'تم تعديل الخدمة بنجاح', [], Service::find($service->id));
    }

    public function deleteService(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "service_id" => 'required',
        ], [
            "service_id.required" => " يجب أدخال رقم الخدمة ",
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $service = Service::find($request["service_id"]);
        if (!$service) {
            return $this->send_response(400, "حصل خطأ في المدخلات", ["service_id" => "رقم الخدمة غير صحيح"], []);
        }
        if (auth()->user()->user_type === 0 || $service->user_id === auth()->user()->id) {
            $service->delete();
            return $this->send_response(200, 'تم حذف الخدمة بنجاح', [], []);
        } else {
            return $this->send_response(400, 'لا يمكنك حذف هذه الخدمة', [], []);
        }
    }
}
