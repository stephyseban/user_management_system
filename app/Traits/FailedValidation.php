<?php

namespace App\Traits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


trait FailedValidation
{

public  function failedValidation(Validator $validator)
{
     if(request()->ajax() || request()->is('web/*')) {
        $errorMessages = [];
        foreach($validator->errors()->toArray() as $key => $value){
           $errorMessages[$key] = implode(',',$value);
        }
        $errorResponse = [
         'success'=> false,
         'msg' => "validation errors", 
         'error_code' => 403,
         'errors'  => $errorMessages
        ];
        throw new HttpResponseException(response()->json($errorResponse, 400));
     }
      
}

}