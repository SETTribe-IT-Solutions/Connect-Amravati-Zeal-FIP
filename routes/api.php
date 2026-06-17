<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Return authenticated user metadata
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // API endpoint for fetching unread notifications
    Route::get('/notifications/unread', function (Request $request) {
        $unread = $request->user()->unreadNotifications;
        return response()->json([
            'count' => $unread->count(),
            'items' => $unread->take(10)
        ]);
    });

    // Mark notification as read
    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        $notif = $request->user()->notifications()->findOrFail($id);
        $notif->markAsRead();
        return response()->json(['success' => true]);
    });
});
