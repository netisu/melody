<?php

namespace App\Http\Controllers\Endpoints;

use App\{
    Http\Controllers\Controller,
    Http\Requests\ProfileUpdateRequest,
    Models\Country,
    Models\User,
    Models\UserBan,
    Models\UserSettings,
    Rules\MatchOldPassword,
};
use Illuminate\{
    Contracts\Auth\MustVerifyEmail,
    Validation\ValidationException,
    Support\Facades\Redirect,
    Support\Facades\Password,
    Http\RedirectResponse,
    Support\Facades\Auth,
    Support\Facades\Hash,
    Http\UploadedFile,
    Http\JsonResponse,
    Support\Facades\Validator,
    Validation\Rules,
    Support\Facades\Storage,
    Support\Carbon,
    Http\Request,
    Support\Str,
};

use Inertia\Inertia;
use Inertia\Response;


use Auth\Events\PasswordReset;


class SettingsController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function changeCountry($country)
    {   
        // Get the user's settings record
        $settings = UserSettings::where('id', Auth::id())->first();

        // Get the country code from the request

        // Check if country exists in the database
        $existingCountry = Country::where('code', $country)->first();

        if ($existingCountry) {
            // Country exists, update settings
            $settings->country_code = strtolower($country);
            $settings->save();

            // Success message (handled by frontend)
            return response()->json([
                "message" => "Country updated successfully.",
                "type" => "success",
            ], 200);
        } else {
            // Country does not exist (example unitedstates instead of us)
            return response()->json([
                "message" => "Invalid country code. Please select a valid country.",
                "type" => "error",
            ], 400); // Use 400 for Bad Request
        }
    }
    /**
     * Update the user's profile information.
     */

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_password_confirmation' => ['same:new_password'],
        ]);

        User::find(Auth::id())->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
            'type' => 'success',
        ], 200);
    }

    private function uploadImage(string $folder, UploadedFile $file, string $name): string
    {
        $path = Storage::disk('spaces')->putFileAs($folder, $file, "{$name}.png", 'public');

        return $path;
    }
    private function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:png', // Validate PNG image
        ]);

        if ($validator->fails()) { // Use Validator object's fails method on $validator
            throw ValidationException::withMessages([$validator->errors()]);
        }
    }

    public function uploadProfilePicture(Request $request)
    {
        $this->validateRequest($request);
        // Get the user

        // Get the user's settings record
        $settings = UserSettings::where('id', Auth::id());

        // Get the image from the request
        $image = $request->image;
        $assetHash = bin2hex(random_bytes(22));

        // Makes sure that its not null (dont think this will stop transparent images bub)
        if (!$image) {
            return response()->json([
                "message" => "Image is null",
                "type" => "danger",
            ], 400); // Use 400 for Bad Request
        }

        $info = getimagesize($request->file('image'));
        if ($info[2] == IMAGETYPE_JPEG) {
            return response()->json([
                'message' => 'This file needs to be a true PNG, none of that sneaky jpeg-in-disguise business.',
                'type' => 'danger' // Optional: specify message type for styling
            ], 502);
        }

        $this->uploadImage('thumbnails/profile-pictures', $request->file('image'), $assetHash);

        $settings->profile_picture_enabled = false;
        $settings->profile_picture_url = $assetHash;

        return redirect()->back();
    }
    public function bannerVisibility(Request $request)
    {
        $request->validate([
            'value' => 'required|boolean',
        ]);

        $user = Auth::user();
        $user->banner_visibility = $request->value;
        $user->save();

        return response()->json(['message' => 'Banner visibility updated successfully']);
    }

    public function uploadBanner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:png,jpg,jpeg|max:2048', // Adjust mime types and size as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $settings = UserSettings::where('id', Auth::id())->first();

        if ($request->hasFile('image')) {
            // Delete the old banner if it exists
            if ($settings->profile_banner_url) {
                Storage::disk('spaces')->delete('uploads/pending' . $settings->profile_banner_url);
            }
            $imgName = bin2hex(random_bytes(22));
            $this->uploadTempImage($request->file('image'), $imgName);

            $settings->profile_banner_enabled = false; 
            $settings->profile_banner_pending = true;
            $settings->profile_banner_url = env(key: 'STORAGE_URL') . '/uploads/pending' . $imgName . '.png'; // Store the public path in the database
            $settings->save();

            return response()->json(['message' => 'Banner uploaded successfully', 'path' => asset($settings->profile_banner_url)]);
        }

        return response()->json(['error' => 'No image file provided'], 400);
    }

    private function uploadTempImage(UploadedFile $file, string $name): string
    {
        $path = Storage::disk('spaces')->putFileAs('uploads/pending', $file, "{$name}.png", 'public');
        return $path;
    }
    /**
     * "Delete" the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        $ban = new UserBan;
        $ban->user_id = $user->id;
        $ban->banner_id = config("Values.system_account_id");
        $ban->type = "self-deletion";
        $ban->active = true;
        $ban->note = "You have requested deletion of your account. To restore in a timely manner, Please contact support.";
        $ban->length = Carbon::createFromTimestamp(time() + 31536000)->format('Y-m-d H:i:s');
        $ban->save();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->with('message', 'Account Deleted Successfully');
    }
}
