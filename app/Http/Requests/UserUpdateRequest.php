<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        if($this->password)
        {
            return [
                'name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->id)],
                'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
                'password_confirmation' => 'min:6'
            ];
        }
        else{
            return [
                'name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->id)],
            ];
        }
    }
}
