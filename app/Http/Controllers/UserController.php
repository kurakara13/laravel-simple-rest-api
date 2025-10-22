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
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->where('active', true);

        // search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // sorting
        $sortBy = $request->input('sortBy', 'created_at');
        $allowedSorts = ['name', 'email', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy);
        } else {
            $query->orderBy('created_at');
        }

        $users = $query->with('orders')->paginate(15);

        $currentUser = $request->user();

        $users->getCollection()->transform(function ($user) use ($currentUser) {
            $user->can_edit = $currentUser ? $currentUser->canEdit($user) : false;
            return $user;
        });

        return response()->json([
            'page' => $users->currentPage(),
            'users' => UserResource::collection($users->items()),
        ]);
    }

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
