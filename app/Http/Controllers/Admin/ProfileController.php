<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfilePasswordRequest;
use App\Http\Requests\Admin\ProfileRequest;
use App\Models\AdminActivity;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private const DIRECTORY = 'uploads/avatars';

    public function edit(): View
    {
        $admin = auth('admin')->user();

        return view('admin.profile.edit', [
            'admin' => $admin,
            'activities' => $admin->activities()->limit(30)->get(),
            'ordersCount' => Order::count(),
            'productsCount' => Product::count(),
            'lastLogin' => $admin->activities()->where('type', 'login')->first(),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $admin = $request->user('admin');
        $data = $request->validated();

        $admin->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);

        if ($admin->isDirty('email')) {
            $admin->email_verified_at = null;
        }

        $avatarChanged = false;

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar((string) $admin->avatar);
            $admin->avatar = $this->storeAvatar($request->file('avatar'));
            $avatarChanged = true;
        } elseif ($request->boolean('remove_avatar') && $admin->avatar) {
            $this->deleteAvatar((string) $admin->avatar);
            $admin->avatar = null;
            $avatarChanged = true;
        }

        $admin->save();

        AdminActivity::record($admin, 'profile', 'Updated profile information', $request);

        if ($avatarChanged) {
            AdminActivity::record($admin, 'avatar', $admin->avatar ? 'Changed profile photo' : 'Removed profile photo', $request);
        }

        return to_route('admin.profile.edit')->with('success', 'Profile updated');
    }

    public function clearActivity(): RedirectResponse
    {
        auth('admin')->user()->activities()->delete();

        return to_route('admin.profile.edit')
            ->with('success', 'Activity log cleared')
            ->with('tab', 'activity');
    }

    public function updatePassword(ProfilePasswordRequest $request): RedirectResponse
    {
        $admin = $request->user('admin');

        $admin->update(['password' => $request->validated('password')]);

        AdminActivity::record($admin, 'password', 'Changed account password', $request);

        return to_route('admin.profile.edit')
            ->with('success', 'Password updated')
            ->with('tab', 'security');
    }

    private function storeAvatar(UploadedFile $file): string
    {
        File::ensureDirectoryExists(public_path(self::DIRECTORY));

        $name = 'avatar-' . Str::uuid() . '.' . ($file->guessExtension() ?: 'png');

        $file->move(public_path(self::DIRECTORY), $name);

        return self::DIRECTORY . '/' . $name;
    }

    private function deleteAvatar(string $path): void
    {
        if (str_starts_with($path, self::DIRECTORY . '/')) {
            File::delete(public_path($path));
        }
    }
}
