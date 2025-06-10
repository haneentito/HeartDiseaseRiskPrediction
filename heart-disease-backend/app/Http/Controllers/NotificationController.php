<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:doctor');
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if (!$notification || $notification->appointment->doctor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized or notification not found'], 403);
        }

        $notification->read = true;
        $notification->save();

        return response()->json(['message' => 'Notification marked as read']);
    }
}