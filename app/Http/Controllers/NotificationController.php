<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $message = $request->input('message');

        event(new NewNotification($message));

        return response()->json([
            'success' => true,
            'message' => 'Thông báo đã được gửi!',
        ]);
    }
}
