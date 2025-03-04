<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()
            ->with([
                'prompt' => 'select_account'
            ])->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            Log::info('Google Callback:', $request->all());
            Log::info('Google User:', (array) $googleUser);
            $user = null;

            DB::beginTransaction();

            $socialAccount = SocialAccount::query()
                ->where([
                    'provider' => SocialAccount::PROVIDER_GOOGLE,
                    'provider_id' => $googleUser->getId()
                ])
                ->first();

            if ($socialAccount) {
                $user = $socialAccount->user;
            } else {
                $user = User::query()
                    ->create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'avatar' => $googleUser->getAvatar(),
                        'password' => '',
                        'email_verified_at' => now(),
                    ]);

                $user->assignRole('member');

                SocialAccount::query()->create([
                    'user_id' => $user->id,
                    'provider' => SocialAccount::PROVIDER_GOOGLE,
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            DB::commit();

            Auth::login($user);
            $token = $user->createToken('API Token')->plainTextToken;

//            $frontendUrl = 'http://localhost:3000/google/callback';
//
//            return redirect()->away(
//                $frontendUrl . "?token=" . urlencode($token) . "&user_id=" . $user->id
//            );
            return redirect()->away("http://localhost:3000/google/callback?token=" . urlencode($token) . "&user_id=" . $user->id);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return redirect()->away('http://localhost:3000/notfound');
        }
    }
}
