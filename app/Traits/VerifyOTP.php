<?php

namespace App\Traits;
use App\Repository\AuthRepository;
use App\Repository\OTPRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

trait VerifyOTP
{

    public function __construct(AuthRepository $authRepo, OTPRepository $otpRepo)
    {
        $this->otpRepo = $otpRepo;
        $this->authRepo = $authRepo;
    }

    public function verifyForgotPasswordOtp($mobile_number, $otp)
    {

        $user_data = User::where('mobile', '=', $mobile_number)
                      ->where('is_deleted',config('nest.common_values.No'))
                      ->orderBy('id', 'desc')
                      ->first();

        $verifyOTPResponse = $this->otpRepo->verifyOTP($user_data->id, $otp, config('nest.otp_type.SMS'));

        if ($verifyOTPResponse) {
            // Check for OTP expiry
            $otpNotExpired = $this->otpRepo->checkOTPExpiry($user_data->id, $otp, config('nest.otp_type.SMS'));
            if ($otpNotExpired) {
                // Check if User Account deleted or not
                if (isset($user_data->is_deleted) && $user_data->is_deleted != config('nest.common_values.No')) {
                    return ['status' => false, 'msg' => "Account is not active"];
                }
                //Destroy OTP
                $this->otpRepo->destroyOTP($user_data->id, config('nest.otp_type.SMS'));
                // Generate Token
                $data = $user_data->createToken("API TOKEN")->plainTextToken;
                return ['status' => true, 'msg' => $data];
            }
            else {
                return ['status' => false, 'msg' => "OTP Expired."];

            }
        }
        return ['status' => false, 'msg' => "Wrong OTP"];


    }
}
