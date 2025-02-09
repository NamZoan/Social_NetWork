<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50|regex:/^[^\d]+$/',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|numeric|digits_between:10,11',
            'day' => 'required|integer|min:1|max:31',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'password' => 'required|min:8',
            'confirmPassword'=>'required|same:password',
        ];
    }

    public function messages()
    {
        return [
            'name.regex'=> 'Nhập đúng dạng tên',
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Nhập đúng dạng của email.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.numeric' => 'Số điện thoại phải là số.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'day.required' => 'Ngày không được để trống.',
            'month.required' => 'Tháng không được để trống.',
            'year.required' => 'Năm không được để trống.',
            'confirmPassword.required' => 'Xác nhận mật khẩu không được để trống.',
            'confirmPassword.same' => 'Mật khẩu không khớp.',
        ];
    }
}
