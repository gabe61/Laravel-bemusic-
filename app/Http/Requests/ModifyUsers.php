<?php namespace App\Http\Requests;

use Common\Core\BaseFormRequest;

class ModifyUsers extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userId = $this->route('id');

        $rules = [
            'first_name'    => 'alpha|min:2|max:255|nullable',
            'last_name'     => 'alpha|min:2|max:255|nullable',
            'permissions'   => 'array',
            'groups'        => 'array',
            'password'      => 'min:3|max:255',
            'email'         => "email|min:3|max:255|unique:users,email,$userId",
        ];

        if ($this->method() === 'POST') {
            $rules['email']    = 'required|'.$rules['email'];
            $rules['password'] = 'required|'.$rules['password'];
        }

        return $rules;
    }
}
