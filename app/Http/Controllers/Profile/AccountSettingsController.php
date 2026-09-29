<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

final class AccountSettingsController extends Controller
{
    /**
     * Display the Account Settings & Appearance page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('profile.account-settings', [
            'user' => $user,
        ]);
    }

    /**
     * Update user profile information (Name, Email).
     */
    public function updateProfile(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'first_name'  => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'email'       => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ];

        // Conditional check: Current password is required only if changing email address
        $isEmailChanged = strtolower(trim((string) $request->input('email'))) !== strtolower((string) $user->email);
        if ($isEmailChanged) {
            $rules['current_password'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        if ($isEmailChanged) {
            if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], (string) $user->password)) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The provided current password does not match our records.',
                        'errors'  => [
                            'current_password' => ['The provided current password does not match our records.'],
                        ],
                    ], 422);
                }

                return back()->withErrors([
                    'current_password' => 'The provided current password does not match our records.',
                ]);
            }
        }

        $firstName = trim($validated['first_name']);
        $middleName = !empty($validated['middle_name']) ? trim($validated['middle_name']) : null;
        $lastName = trim($validated['last_name']);

        // Synchronize full name
        $fullName = trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);

        $user->update([
            'first_name'  => $firstName,
            'middle_name' => $middleName,
            'last_name'   => $lastName,
            'name'        => $fullName,
            'email'       => strtolower(trim($validated['email'])),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile information updated successfully.',
                'user'    => [
                    'name'        => $user->name,
                    'first_name'  => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'last_name'   => $user->last_name,
                    'email'       => $user->email,
                ],
            ]);
        }

        return redirect()->route('account.settings')
            ->with('success', 'Profile information updated successfully.');
    }

    /**
     * Upload or update user avatar photo.
     * Supports both Base64 Data URL and multipart file uploads.
     */
    public function updatePhoto(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // 1. Process Base64 Data URL (instant, optimized client-side downscale)
        if ($request->filled('photo_base64')) {
            $base64Data = (string) $request->input('photo_base64');

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                $rawExt = strtolower($matches[1]);
                $ext = match ($rawExt) {
                    'jpeg', 'jpg' => 'jpg',
                    'png'         => 'png',
                    'webp'        => 'webp',
                    'gif'         => 'gif',
                    default       => 'jpg',
                };

                $binary = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1), true);

                if ($binary === false || strlen($binary) === 0) {
                    return response()->json(['success' => false, 'message' => 'Invalid image data provided.'], 422);
                }

                // Delete old avatar if custom uploaded photo exists
                if (!empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
                    Storage::disk('public')->delete($user->avatar_path);
                }

                $filename = 'avatars/' . \Illuminate\Support\Str::random(40) . '.' . $ext;
                Storage::disk('public')->put($filename, $binary);

                $user->update([
                    'avatar_path' => $filename,
                ]);

                $newUrl = Storage::url($filename);

                return response()->json([
                    'success'    => true,
                    'message'    => 'Profile photo updated successfully.',
                    'avatar_url' => $newUrl,
                ]);
            }
        }

        // 2. Process standard multipart file upload
        if (!$request->hasFile('photo')) {
            $isOversized = isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 2 * 1024 * 1024;
            $errorMsg = $isOversized
                ? 'The photo exceeds the server upload limit. Please select an image under 10MB.'
                : 'No photo file was detected. Please choose a valid image file.';

            return response()->json(['success' => false, 'message' => $errorMsg], 422);
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
        ]);

        // Delete old avatar if custom uploaded photo exists
        if (!empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('photo')->store('avatars', 'public');

        $user->update([
            'avatar_path' => $path,
        ]);

        $newUrl = Storage::url($path);

        return response()->json([
            'success'    => true,
            'message'    => 'Profile photo updated successfully.',
            'avatar_url' => $newUrl,
        ]);
    }

    /**
     * Remove custom profile photo and revert to initial avatar.
     */
    public function removePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update([
            'avatar_path' => null,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Profile photo removed.',
            'avatar_url' => $user->avatarUrl(),
        ]);
    }

    /**
     * Update user theme preference (light, dark, system).
     */
    public function updateTheme(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
        ]);

        $user = $request->user();
        $user->update([
            'theme_preference' => $validated['theme'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'theme'   => $validated['theme'],
                'message' => 'Appearance theme updated to ' . ucfirst($validated['theme']) . '.',
            ]);
        }

        return redirect()->route('account.settings')
            ->with('success', 'Appearance theme updated.');
    }
}
