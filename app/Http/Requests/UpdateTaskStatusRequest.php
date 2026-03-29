<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(StatusEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'A task status is required.',
            'status.enum' => 'The status must be pending, in_progress, or done.',
        ];
    }
}
