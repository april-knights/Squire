<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfile extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO: Authorization
        return true;
    }

    /**
     * Unique except from existing entry for this knight.
     *
     * @param array $knight
     * @return Rule
     */
    private static function unique($rname) {
        return Rule::unique('knight')->ignore($rname, 'rname');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rname = $this->route('rname');

        return [
            'rname' => [
                'required',
                'max:30',
                self::unique($rname),
            ],
            'dname' => [
                'nullable',
                'max:40',
                'regex:/^.+?#\d{4}/',
                self::unique($rname),
            ],
            'email' => [
                'nullable',
                'email',
                'max:50',
                self::unique($rname),
            ],
            'batt' => [
                'nullable',
                'integer',
                'exists:battalion,pkey',
            ],
            'rank' => [
                'nullable',
                'integer',
                'exists:krank,pkey',
            ],
            'divs' => [
                'nullable',
            ],
            'divs.*' => [
                'integer',
                'exists:division,pkey',
            ],
            'firstevent' => [
                'nullable',
                'integer',
                'exists:event,pkey',
            ],
            'skills' => [
                'nullable',
            ],
            'skills.*' => [
                'integer',
                'exists:skill,pkey',
                // TODO: Check if not parent rule.
            ],
            'bio' => [
                'nullable',
                'string',
                'max:255',
            ],
            'rlimpact' => [
                'nullable',
                'string',
                'max:255',
            ]
        ];
    }
}
