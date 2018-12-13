<?php namespace App\Http\Requests;

use Validator;
use Common\Core\BaseFormRequest;

class ModifyPlaylist extends BaseFormRequest
{

    public function messages() {
        return [
            'unique_name' => 'You have already created a playlist with this name.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('uniqueName', function ($attribute, $value) {
            $playlistId = $this->route('id');
            $playlist = $this->user() ? $this->user()->playlists()->where('name', $value)->first() : null;
            return ! $playlist || $playlist->id === (int) $playlistId;
        });

        $rules =  [
            'name' => 'string|min:3|max:255|unique_name',
            'description' => 'min:20|max:170|nullable',
            'public' => 'boolean',
        ];

        if ($this->method() === 'POST') {
            $rules['name'] = 'required|' . $rules['name'];
        }

        return $rules;
    }
}
