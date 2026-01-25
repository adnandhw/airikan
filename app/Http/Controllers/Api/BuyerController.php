<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuyerController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:60',
            'lastName' => 'required|string|max:60',
            'phone' => 'required|string|max:255|unique:buyers',
            'password' => 'required|string|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/',
            'provinceId' => 'nullable|string',
            'regencyId' => 'nullable|string',
            'districtId' => 'nullable|string',
            'villageId' => 'nullable|string',
            'address' => 'nullable|string',
            'postalCode' => 'nullable|string',
        ], [
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.regex' => 'Password harus kombinasi huruf besar, huruf kecil, dan angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verificationToken = \Illuminate\Support\Str::random(60);

            $buyer = Buyer::create([
                'name' => $request->firstName . ' ' . $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'province_id' => $request->provinceId,
                'city_id' => $request->regencyId,
                'district_id' => $request->districtId,
                'village_id' => $request->villageId,
                'address' => $request->address,
                'postal_code' => $request->postalCode,
                'verification_token' => $verificationToken,
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to($buyer->email)->send(new \App\Mail\VerificationMail($buyer, $verificationToken));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
                // Continue even if mail fails, user can resend later
            }

            return response()->json([
                'success' => true,
                'data' => $buyer,
                'message' => 'Registration successful'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create buyer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // Can be email or phone
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $identifier = $request->identifier;
        $password = $request->password;
        $hashedPassword = hash('sha256', $password);

        // Find buyer by email or phone
        $buyer = Buyer::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$buyer) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $buyer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 401);
        }

        if (is_null($buyer->email_verified_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Email belum diverifikasi. Silakan cek inbox email Anda.',
                'unverified' => true,
                'email' => $buyer->email
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $buyer,
        ], 200);
    }

    public function show($id)
    {
        $buyer = Buyer::find($id);

        if (!$buyer) {
            return response()->json([
                'success' => false,
                'message' => 'Buyer not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $buyer,
        ], 200);
    }
    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Log::info('Update Buyer Request', ['id' => $id, 'data' => $request->all()]);

        $buyer = Buyer::find($id);

        if (!$buyer) {
            \Illuminate\Support\Facades\Log::error('Buyer not found for update', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Buyer not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'firstName' => 'sometimes|required|string|max:60',
            'lastName' => 'sometimes|required|string|max:60',
            'email' => 'sometimes|required|string|email|max:255|unique:buyers,email,' . $id,
            'phone' => 'sometimes|required|string|max:255|unique:buyers,phone,' . $id,
            'provinceId' => 'nullable|string',
            'regencyId' => 'nullable|string',
            'districtId' => 'nullable|string',
            'villageId' => 'nullable|string',
            'address' => 'nullable|string',
            'postalCode' => 'nullable|string',
            'password' => 'sometimes|string|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/',
        ], [
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.regex' => 'Password harus kombinasi huruf besar, huruf kecil, dan angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Use fill to only update fields that are present in the request
            // Manual fill for snake_case mapping
            if ($request->has('firstName') || $request->has('lastName')) {
                $buyer->name = ($request->has('firstName') ? $request->firstName : $buyer->first_name) . ' ' . ($request->has('lastName') ? $request->lastName : $buyer->last_name);
            }
            if ($request->has('email')) $buyer->email = $request->email;
            if ($request->has('phone')) $buyer->phone = $request->phone;
        
            // Map camelCase to snake_case for location
            if ($request->has('provinceId')) $buyer->province_id = $request->provinceId;
            if ($request->has('regencyId')) $buyer->city_id = $request->regencyId;
            if ($request->has('districtId')) $buyer->district_id = $request->districtId;
            if ($request->has('villageId')) $buyer->village_id = $request->villageId;
            if ($request->has('postalCode')) $buyer->postal_code = $request->postalCode;
            if ($request->has('address')) $buyer->address = $request->address;
            
            // Handle other fields
            if ($request->has('reseller_status')) $buyer->reseller_status = $request->reseller_status;

            // Remove direct fill call which uses camelCase keys from request
            // $buyer->fill($request->only([...]));

            // Only update password if provided
            if ($request->has('password') && !empty($request->input('password'))) {
                 // Check if current password is provided and matches
                 if ($request->has('current_password') && !empty($request->input('current_password'))) {
                     $currentPassword = $request->input('current_password');
                     if (!\Illuminate\Support\Facades\Hash::check($currentPassword, $buyer->password)) {
                         return response()->json([
                             'success' => false,
                             'message' => 'Password lama salah.'
                         ], 400); // Bad Request
                     }
                 } else {
                     // Require current password for password changes? 
                     // Based on user request "kolom pertama adalah masukan password lama", it seems mandatory.
                     return response()->json([
                         'success' => false,
                         'message' => 'Password lama wajib diisi.'
                     ], 400);
                 }

                 $buyer->password = \Illuminate\Support\Facades\Hash::make($request->input('password'));
            }

            $buyer->save();
            
            \Illuminate\Support\Facades\Log::info('Buyer updated successfully', ['buyer' => $buyer]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $buyer
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $buyer = Buyer::where('email', $request->identifier)->first();

        if (!$buyer) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar.',
            ], 404);
        }

        $token = \Illuminate\Support\Str::random(60);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $buyer->email],
            [
                'email' => $buyer->email,
                'token' => \Illuminate\Support\Facades\Hash::make($token),
                'created_at' => now()
            ]
        );

        try {
            $resetLink = url("/reset-password?token={$token}&email=" . urlencode($buyer->email));
            \Illuminate\Support\Facades\Log::info("MANUAL RESET LINK for {$buyer->email}: {$resetLink}");

            \Illuminate\Support\Facades\Mail::to($buyer->email)->send(new \App\Mail\ResetPasswordMail($token, $buyer));
            
            return response()->json([
                'success' => true,
                'message' => 'Tautan reset kata sandi telah dikirim ke email Anda.',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/',
        ], [
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.regex' => 'Password harus kombinasi huruf besar, huruf kecil, dan angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Verify Token
        $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !\Illuminate\Support\Facades\Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau kadaluarsa.',
            ], 400);
        }

        // Check expiration (e.g., 60 minutes)
        if (\Carbon\Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Token telah kadaluarsa.',
            ], 400);
        }

        // Update Password
        $buyer = Buyer::where('email', $request->email)->first();
        if (!$buyer) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $buyer->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $buyer->save();

        // Delete Token
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login.',
        ]);
    }
    public function verifyEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Find by email first
        $buyer = Buyer::where('email', $request->email)->first();

        if (!$buyer) {
            return response()->json(['success' => false, 'message' => 'Email tidak valid.'], 400);
        }

        // Check if already verified
        if ($buyer->email_verified_at) {
            return response()->json(['success' => true, 'message' => 'Email sudah diverifikasi sebelumnya.']);
        }

        // Check token
        if ($buyer->verification_token !== $request->token) {
            return response()->json(['success' => false, 'message' => 'Token verifikasi tidak valid.'], 400);
        }

        $buyer->email_verified_at = now();
        $buyer->verification_token = null; // Clear token after usage
        $buyer->save();

        return response()->json(['success' => true, 'message' => 'Email berhasil diverifikasi. Silakan login.']);
    }

    public function resendVerification(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $buyer = Buyer::where('email', $request->email)->first();

        if (!$buyer) {
            return response()->json(['success' => false, 'message' => 'Email tidak terdaftar.'], 404);
        }

        if ($buyer->email_verified_at) {
            return response()->json(['success' => false, 'message' => 'Email sudah diverifikasi.'], 400);
        }

        $token = \Illuminate\Support\Str::random(60);
        $buyer->verification_token = $token;
        $buyer->save();

        try {
            \Illuminate\Support\Facades\Mail::to($buyer->email)->send(new \App\Mail\VerificationMail($buyer, $token));
            return response()->json(['success' => true, 'message' => 'Link verifikasi telah dikirim ulang.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email.'], 500);
        }
    }
}
