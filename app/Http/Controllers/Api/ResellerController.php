<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResellerController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'businessName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:resellers',
            'phone' => 'required|string|max:20|unique:resellers',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reseller = Reseller::create([
                'name' => $request->name,
                'businessName' => $request->businessName,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reseller registered successfully',
                'data' => $reseller
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register reseller: ' . $e->getMessage()
            ], 500);
        }
    }
}
