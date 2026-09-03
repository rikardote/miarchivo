<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RearchiveBulkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('loans.return') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expedient_ids' => ['required_without:codes', 'nullable', 'array'],
            'expedient_ids.*' => ['integer', 'exists:expedients,id'],
            'codes' => ['required_without:expedient_ids', 'nullable', 'array'],
            'codes.*' => ['string'],
        ];
    }
}
