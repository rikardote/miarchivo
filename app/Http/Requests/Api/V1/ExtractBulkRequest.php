<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExtractBulkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('loans.deliver') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'loan_ids' => ['required_without:codes', 'nullable', 'array'],
            'loan_ids.*' => ['integer', 'exists:loan_requests,id'],
            'codes' => ['required_without:loan_ids', 'nullable', 'array'],
            'codes.*' => ['string'],
        ];
    }
}
