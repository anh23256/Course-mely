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
use Illuminate\Support\Str;
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
                $existingUser = User::query()->where('email', $googleUser->getEmail())->first();

                if ($existingUser) {
                    $user = $existingUser;

                    if (empty($user->avatar) && $googleUser->getAvatar()) {
                        $user->update(['avatar' => $googleUser->getAvatar()]);
                    }

                    SocialAccount::query()->create([
                        'user_id' => $user->id,
                        'provider' => SocialAccount::PROVIDER_GOOGLE,
                        'provider_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);

                } else {
                    $user = User::query()
                        ->create([
                            'code' => substr(str_replace('-', '', Str::uuid()->toString()), 0, 10),
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
            }

            DB::commit();

            Auth::login($user);
            $token = $user->createToken('API Token')->plainTextToken;

            return redirect()->away("http://localhost:3000/google/callback?token=" . $token);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return redirect()->away('http://localhost:3000/not-found');
        }
    }
}
