<?php

namespace App\Http\Requests;

use App\Enums\PriorityEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks')->where(function ($query) {
                    return $query->where('due_date', $this->due_date);
                }),
            ],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'priority' => ['required', Rule::enum(PriorityEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'A task with this title already exists on the selected due date.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
        ];
    }
}
