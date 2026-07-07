<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePlayerId implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = auth()->id();

        $exists = User::query()
            ->where('player_id', $value)
            ->where('id', '!=', $userId)
            ->exists();

        if ($exists) {
            $fail('Denne spiller er allerede tilknyttet en anden bruger.');
        }
    }
}
