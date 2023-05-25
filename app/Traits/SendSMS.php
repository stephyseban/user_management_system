<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Services\SMS\SMSManager;
use App\Services\SMSTemplate\SMSTemplateManager;

trait SendSMS
{


    public function SendSMS($templateName, $user_data, $otp, $country_code_data)
    {
        if ($user_data != "" &&  $otp != "") {
            $message = SMSTemplateManager::generateMessage(config('nest.sms_templates.' . $templateName), ['parameter_1' => $otp]);
            // Send SMS
            $sendSMSResponse = SMSManager::send($country_code_data->dial_code, $country_code_data->dial_code . $user_data->mobile, $message);
            if ($sendSMSResponse) {
                return $sendSMSResponse;
            } else {
                return ['status' => false, 'msg' => "User doesn't have valid phone number."];
            }
        }
    }
}
