<?php

use App\Models\Admin;
use App\Models\AdminActivity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

function profileAdmin(array $attributes = []): Admin
{
    return Admin::forceCreate($attributes + [
        'name' => 'Faisal Ahmed',
        'email' => 'profile-admin@example.com',
        'password' => bcrypt('secret-pass-1'),
        'job_title' => 'Super Admin',
    ]);
}

afterEach(function () {
    File::deleteDirectory(public_path('uploads/avatars'));
});

it('requires an admin session to see the profile', function () {
    $this->get(route('admin.profile.edit'))->assertRedirect(route('admin.login'));
});

it('shows the profile page', function () {
    $this->actingAs(profileAdmin(), 'admin')
        ->get(route('admin.profile.edit'))
        ->assertOk()
        ->assertSee('Faisal Ahmed')
        ->assertSee('Super Admin');
});

it('updates profile details and logs the activity', function () {
    $admin = profileAdmin();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.profile.update'), [
            'name' => 'Faisal Rahman',
            'email' => 'new@example.com',
            'phone' => '+880 1700-000000',
            'bio' => 'Store owner.',
        ])
        ->assertRedirect(route('admin.profile.edit'))
        ->assertSessionHas('success');

    $admin->refresh();

    expect($admin->name)->toBe('Faisal Rahman')
        ->and($admin->email)->toBe('new@example.com')
        ->and($admin->phone)->toBe('+880 1700-000000')
        ->and($admin->job_title)->toBe('Super Admin')
        ->and(AdminActivity::where('admin_id', $admin->id)->where('type', 'profile')->exists())->toBeTrue();
});

it('does not let an admin change their own role title', function () {
    $admin = profileAdmin();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.profile.update'), ['name' => 'Faisal', 'email' => $admin->email, 'job_title' => 'Hacker']);

    expect($admin->refresh()->job_title)->toBe('Super Admin');
});

it('rejects an email used by another admin', function () {
    profileAdmin(['email' => 'taken@example.com']);
    $admin = profileAdmin(['email' => 'mine@example.com']);

    $this->actingAs($admin, 'admin')
        ->put(route('admin.profile.update'), ['name' => 'Faisal', 'email' => 'taken@example.com'])
        ->assertSessionHasErrors('email');
});

it('uploads and removes a profile photo', function () {
    $admin = profileAdmin();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.profile.update'), [
            'name' => 'Faisal',
            'email' => $admin->email,
            'avatar' => UploadedFile::fake()->image('me.png', 200, 200),
        ])
        ->assertSessionHasNoErrors();

    $path = $admin->refresh()->avatar;

    expect($path)->toStartWith('uploads/avatars/')
        ->and(File::exists(public_path($path)))->toBeTrue();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.profile.update'), ['name' => 'Faisal', 'email' => $admin->email, 'remove_avatar' => '1']);

    expect($admin->refresh()->avatar)->toBeNull()
        ->and(File::exists(public_path($path)))->toBeFalse();
});

it('changes the password with the correct current password', function () {
    $admin = profileAdmin();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.profile.password'), [
            'current_password' => 'secret-pass-1',
            'password' => 'brand-new-pass-2',
            'password_confirmation' => 'brand-new-pass-2',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.profile.edit'));

    expect(Hash::check('brand-new-pass-2', $admin->refresh()->password))->toBeTrue()
        ->and(AdminActivity::where('type', 'password')->count())->toBe(1);
});

it('rejects a wrong current password', function () {
    $admin = profileAdmin();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.profile.password'), [
            'current_password' => 'wrong-pass',
            'password' => 'brand-new-pass-2',
            'password_confirmation' => 'brand-new-pass-2',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('secret-pass-1', $admin->refresh()->password))->toBeTrue();
});

it('records a login activity when an admin signs in', function () {
    $admin = profileAdmin();

    $this->post(route('admin.login'), ['email' => $admin->email, 'password' => 'secret-pass-1'])
        ->assertRedirect(route('admin.dashboard'));

    expect(AdminActivity::where('admin_id', $admin->id)->where('type', 'login')->exists())->toBeTrue();
});
