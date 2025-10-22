<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\AdminUserCreatedNotification;
use App\Notifications\UserCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class UserController extends Controller
{
    public function store(CreateUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create($validated);

        // send email to user
        $user->notify(new UserCreatedNotification());

        // get admin email from config
        $adminEmail = config('mail.admin_email', 'admin@example.com');

        // send notification to admin
        Notification::route('mail', $adminEmail)
            ->notify(new AdminUserCreatedNotification($user->name, $user->email));

        return response()->json(new UserResource($user), 201);
    }
}
