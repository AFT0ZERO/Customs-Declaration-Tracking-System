<?php

namespace App\Http\Requests;

use App\Models\CustomDeclaration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomDeclarationRequest extends FormRequest
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
        $declarationId = $this->route('id');
        $declaration = CustomDeclaration::find($declarationId);

        // Use new values if provided, otherwise use existing values
        $declarationType = $this->declaration_type ?? $declaration?->declaration_type ?? '';
        $year = $this->year ?? $declaration?->year ?? '';

        return [
            'editNumber' => [
                'required',
                'string',
                'max:255',
                Rule::unique('custom_declarations', 'declaration_number')
                    ->where('declaration_type', $declarationType)
                    ->where('year', $year)
                    ->ignore($declarationId),
            ],
            'declaration_type' => ['required', 'string', 'max:50', 'in:220,224,900'],
            'year' => ['required', 'integer', 'in:2025,2026'],
            'status' => ['required', 'string', 'max:255'],
            'editDescription' => ['nullable', 'string'],
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
            'editNumber.required' => 'رقم البيان مطلوب.',
            'editNumber.unique' => 'هذا البيان الجمركي موجود بالفعل مع نفس النوع والسنة.',
            'declaration_type.required' => 'نوع البيان الجمركي مطلوب.',
            'declaration_type.in' => 'نوع البيان الجمركي يجب أن يكون 220 أو 224 أو 900.',
            'year.required' => 'السنة مطلوبة.',
            'year.in' => 'السنة يجب أن تكون 2025 أو 2026.',
            'status.required' => 'الحالة مطلوبة.',
        ];
    }
}
