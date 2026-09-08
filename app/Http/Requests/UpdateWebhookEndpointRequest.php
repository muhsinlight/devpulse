<?php

namespace App\Http\Requests;

use App\Models\WebhookEndpoint;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookEndpointRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $webhookEndpoint = $this->route('webhookEndpoint');

        return $webhookEndpoint instanceof WebhookEndpoint
            && ($this->user()?->can('update', $webhookEndpoint) ?? false);
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
            'is_active' => ['sometimes', 'boolean'],
            'hmac_required' => ['sometimes', 'boolean'],
        ];
    }
}
