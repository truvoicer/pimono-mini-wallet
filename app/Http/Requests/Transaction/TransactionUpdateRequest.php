<?php

namespace App\Http\Requests\Transaction;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'receiver_id' => [
                'sometimes',
                'integer',
                Rule::exists(User::class, 'id'),
            ],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
        ];
    }
}
