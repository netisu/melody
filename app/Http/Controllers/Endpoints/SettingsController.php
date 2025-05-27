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
use App\Models\Avatar;
use App\Models\Followers;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Item;
use App\Models\ItemPurchase;
use App\Models\Message;
use App\Models\Space;
use App\Models\SpaceMembers;
use App\Models\Status;
use App\Models\UsernameHistory;
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
        $settings = UserSettings::where('id', Auth::id())->first();

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

        $settings = UserSettings::where('id', Auth::id())->first();
        $settings->profile_banner_enabled = $request->value;
        $settings->save();

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
            $settings->profile_banner_url = config('app.storage.url') . '/uploads/pending' . $imgName . '.png'; // Store the public path in the database
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

        $user = User::where('id', Auth::id())->first();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::guard('web')->logout();
        // Forum Related Deletion
        ForumReply::where('creator_id', $user->id)->delete();
        ForumThread::whereIn('post_id', $user->id)->delete();

        // Item Related Deletion
        Item::where('creator_id', $user->id)->delete();
        ItemPurchase::where('buyer_id', $user->id)->delete();

        // Follower Related Deletion
        Followers::where('follower_id', $user->id)->delete();
        Followers::where('following_id', $user->id)->delete();

        // Message Related Deletion
        Message::where('sent_from', $user->id)->delete();
        Message::where('sent_to', $user->id)->delete();

        // Space Related Deletion
        Space::where('owner_id', $user->id)->delete();
        SpaceMembers::where('user_id', $user->id)->delete();

        // Status Related Deletion
        Status::where('creator_id', $user->id)->delete();

        // Avatar Related Deletion
        Avatar::where('user_id', $user->id)->delete();

        // Clear User Bans
        UserBan::where('user_id', $user->id)->delete();

        // Setting Related Deletion
        UserSettings::where('user_id', $user->id)->delete();
        UsernameHistory::where('user_id', $user->id)->delete();

        $user->delete();

        return back()->with('message', 'Account Deleted Successfully');
    }
}
