<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\BillingCycle;
use App\Models\Plan;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveClientRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('active', true)],
            'cycle' => ['required', Rule::enum(BillingCycle::class)],
            'start_date' => ['required', 'date'],
            'discount' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    public function plan(): Plan
    {
        return Plan::findOrFail($this->integer('plan_id'));
    }

    public function cycle(): BillingCycle
    {
        return $this->enum('cycle', BillingCycle::class);
    }

    public function startDate(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->string('start_date')->value());
    }

    public function discount(): float
    {
        return $this->float('discount');
    }
}
