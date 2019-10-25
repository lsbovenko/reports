<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlannedHoursRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'planned_hours' => 'required|array|size:12',
            //31 - maximum number of days in a month, 8 - maximum number of working hours per day under the law
            'planned_hours.*' => 'required|integer|between:' . 1 . ',' . 31*8,
        ];
    }
}
