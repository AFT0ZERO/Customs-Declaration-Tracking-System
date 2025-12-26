<?php

namespace App\Http\Requests;

use App\Models\CustomDeclaration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomDeclarationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'declaration_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('custom_declarations')->where(function ($query) {
                    return $query->where('declaration_type', $this->declaration_type)
                        ->where('year', $this->year);
                }),
            ],
            'declaration_type' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'status' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'declaration_number.required' => 'رقم البيان الجمركي مطلوب.',
            'declaration_number.unique' => 'هذا البيان الجمركي موجود بالفعل مع نفس النوع والسنة.',
            'declaration_type.required' => 'نوع البيان الجمركي مطلوب.',
            'declaration_type.max' => 'نوع البيان الجمركي يجب ألا يتجاوز 50 حرفاً.',
            'year.required' => 'السنة مطلوبة.',
            'year.integer' => 'السنة يجب أن تكون رقماً صحيحاً.',
            'year.min' => 'السنة يجب أن تكون 1900 أو أحدث.',
            'year.max' => 'السنة يجب ألا تتجاوز ' . (date('Y') + 1) . '.',
            'status.required' => 'الحالة مطلوبة.',
        ];
    }
}

