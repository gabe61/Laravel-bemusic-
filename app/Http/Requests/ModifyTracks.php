<?php namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Common\Core\BaseFormRequest;

class ModifyTracks extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $trackId = $this->route('id');
        $albumName = $this->request->get('album_name', '');

        $rules = [
            'name' => [
                'required', 'string', 'min:1', 'max:255',
                Rule::unique('tracks')->where(function(Builder $query) use($albumName) {
                    $query->where('album_name', $albumName);
                })->ignore($trackId)
            ],
            'number'             => 'required|min:1',
//            'album_name'         => 'required|min:1|max:255',
            'duration'           => 'required|integer|min:1',
//            'artists'            => 'required|string|min:1|max:255',
            'spotify_popularity' => 'min:1|max:100|nullable',
//            'album_id'           => 'required|min:1|exists:albums,id',
        ];
        if($this->request->get('withoutAlbum') !== TRUE)
        {
            $rules['album_name'] = 'required|min:1|max:255';
            $rules['album_id'] = 'required|min:1|max:255';
            $rules['artists'] = 'required|string|min:1|max:255';
        }

        return $rules;
    }
}
