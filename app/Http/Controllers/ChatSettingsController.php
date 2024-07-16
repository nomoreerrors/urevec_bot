<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatSettingsService;

class ChatSettingsController extends Controller
{
    public function switchPermissionsNightLightMode(Request $request)
    {
        $requestData = $request->all();
        ChatSettingsService::setNightLightMode($requestData);
    }
}
