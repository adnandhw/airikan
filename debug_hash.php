<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$email = 'debug@example.com';
$token = Str::random(60);
$hashedToken = Hash::make($token);

echo "Token: $token\n";
echo "Hashed: $hashedToken\n";

if (Hash::check($token, $hashedToken)) {
    echo "Direct check: OK\n";
} else {
    echo "Direct check: FAIL\n";
}

DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $email],
    [
        'email' => $email,
        'token' => $hashedToken,
        'created_at' => now()
    ]
);

$record = DB::table('password_reset_tokens')->where('email', $email)->first();
echo "Stored Hash: " . $record->token . "\n";

if (Hash::check($token, $record->token)) {
    echo "Stored check: OK\n";
} else {
    echo "Stored check: FAIL\n";
}
