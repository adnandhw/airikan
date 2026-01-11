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
            'email' => 'required|string|email|max:255|unique:buyers',
            'phone' => 'required|string|max:255|unique:buyers',
            'password' => 'required|string',
            'provinceId' => 'nullable|string',
            'regencyId' => 'nullable|string',
            'districtId' => 'nullable|string',
            'villageId' => 'nullable|string',
            'address' => 'nullable|string',
            'postalCode' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $buyer = Buyer::create([
                'name' => $request->firstName . ' ' . $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'phone' => $request->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'province_id' => $request->provinceId,
                'city_id' => $request->regencyId,
                'district_id' => $request->districtId,
                'village_id' => $request->villageId,
                'address' => $request->address,
                'postal_code' => $request->postalCode,
            ]);

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
}
