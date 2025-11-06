<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Get all users (admin can see all, staff can see only staff)
     */
    public function index()
    {
        try {
            $currentUserId = session('admin_id');
            $currentRole = session('role');

            if (!$currentUserId || !$currentRole) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $currentUser = Admin::find($currentUserId);
            if (!$currentUser) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Admin can see everyone, staff can only see themselves
            if ($currentRole === 'admin') {
                $users = Admin::select('id', 'username', 'role', 'is_seeded', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $users = Admin::select('id', 'username', 'role', 'is_seeded', 'created_at')
                    ->where('id', $currentUserId)
                    ->get();
            }

            $users = $users->map(function ($user) use ($currentUserId, $currentRole) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                    'is_seeded' => $user->is_seeded,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    // Admin can't be deleted if seeded, staff accounts can be deleted by admin
                    'can_delete' => $currentRole === 'admin' && 
                                   $user->role === 'staff' && 
                                   $user->id !== $currentUserId,
                ];
            });

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch users: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new user (admin only, can only create staff)
     */
    public function store(Request $request)
    {
        try {
            $currentRole = session('role');
            
            if ($currentRole !== 'admin') {
                return response()->json(['error' => 'Only admins can create accounts'], 403);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:admins,username|min:3|max:50|regex:/^[a-zA-Z0-9_-]+$/',
                'password' => 'required|string|min:6|max:255',
                'role' => 'required|in:staff', // Admin can only create staff
            ], [
                'username.regex' => 'Username can only contain letters, numbers, underscores, and hyphens.',
                'username.unique' => 'This username is already taken.',
                'password.min' => 'Password must be at least 6 characters long.',
                'role.in' => 'You can only create staff accounts.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Admin::create([
                'username' => trim($request->username),
                'password' => Hash::make($request->password),
                'role' => 'staff', // Force staff role
                'is_seeded' => false,
            ]);

            Log::info('User created', [
                'created_by' => session('username'),
                'new_user' => $user->username,
                'role' => $user->role
            ]);

            return response()->json([
                'message' => 'Staff account created successfully',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, $id)
    {
        try {
            $currentUserId = session('admin_id');
            $currentRole = session('role');

            if (!$currentUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $targetUser = Admin::find($id);
            if (!$targetUser) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Admin can change any password, staff can only change their own
            if ($currentRole !== 'admin' && $currentUserId != $id) {
                return response()->json(['error' => 'You can only change your own password'], 403);
            }

            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:6|max:255',
            ], [
                'password.min' => 'Password must be at least 6 characters long.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $targetUser->update([
                'password' => Hash::make($request->password)
            ]);

            Log::info('Password updated', [
                'updated_by' => session('username'),
                'target_user' => $targetUser->username
            ]);

            return response()->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error updating password: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update password: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete user (admin can only delete staff accounts)
     */
    public function destroy($id)
    {
        try {
            $currentUserId = session('admin_id');
            $currentRole = session('role');

            if ($currentRole !== 'admin') {
                return response()->json(['error' => 'Only admins can delete accounts'], 403);
            }

            $targetUser = Admin::find($id);
            if (!$targetUser) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Cannot delete yourself
            if ($currentUserId == $id) {
                return response()->json(['error' => 'You cannot delete your own account'], 403);
            }

            // Cannot delete admin accounts
            if ($targetUser->role === 'admin') {
                return response()->json(['error' => 'Admin accounts cannot be deleted'], 403);
            }

            // Cannot delete seeded accounts
            if ($targetUser->is_seeded) {
                return response()->json(['error' => 'Seeded accounts cannot be deleted'], 403);
            }

            $deletedUsername = $targetUser->username;
            $targetUser->delete();

            Log::info('User deleted', [
                'deleted_by' => session('username'),
                'deleted_user' => $deletedUsername
            ]);

            return response()->json(['message' => 'Staff account deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }
}