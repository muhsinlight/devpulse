<?php

namespace App\Http\Requests;

use App\Enums\HttpMethod;
use App\Models\Monitor;
use App\Rules\PublicHttpUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMonitorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $monitor = $this->route('monitor');

        return $monitor instanceof Monitor
            && ($this->user()?->can('update', $monitor) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048', new PublicHttpUrl],
            'method' => ['required', Rule::enum(HttpMethod::class)],
            'headers' => ['nullable', 'array', 'max:20'],
            'headers.*' => ['required', 'string', 'max:2000'],
            'body' => ['nullable', 'string', 'max:10000'],
            'check_interval' => ['required', 'integer', Rule::in([1, 5, 15, 30, 60])],
            'expected_status_code' => ['required', 'integer', 'between:100,599'],
            'timeout_seconds' => ['required', 'integer', 'between:1,60'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
