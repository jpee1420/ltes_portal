<?php

namespace App\Actions\UserManagement;

use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadUserPhoto
{
    /**
     * Store an uploaded photo, clean up old avatar file, and update user record.
     */
    public function handle(User $user, UploadedFile $file): string
    {
        // Remove existing photo if present
        if ($user->avatar) {
            $relativePath = Str::after($user->avatar, '/storage/');
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename = "{$user->id}_".time().'_'.Str::random(8).".{$extension}";

        $path = $file->storeAs('photos/users', $filename, 'public');
        $avatarUrl = Storage::url($path);

        $user->forceFill([
            'avatar' => $avatarUrl,
        ])->save();

        AuditLogger::log('user.photo_updated', "Uploaded ID photo for user {$user->name} ({$user->credential})", $user);

        return $avatarUrl;
    }
}
