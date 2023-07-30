<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Filter;
use App\Traits\Search;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kutia\Larafirebase\Facades\Larafirebase;



class NotificationController extends Controller
{
    use SendResponse, Pagination, UploadImage, Search, Filter, OrderBy;

    public function sendMessage(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [

            'title' => 'required',
            'body' => 'required',
        ], [
            'title.required' => 'title  is required',
            'body.required' => 'body is required',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $deviceTokens = User::whereNotNull('fcmtoken')->pluck('fcmtoken')->toArray();
        return Larafirebase::withTitle($request['title'])
            ->withBody($request['body'])
            ->sendNotification($deviceTokens);
    }
}
