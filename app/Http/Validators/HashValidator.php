<?php namespace App\Http\Validators;

use Hash;

class HashValidator {

    public function validate($attribute, $value, $parameters) {
        return Hash::check($value, $parameters[0]);
    }
}