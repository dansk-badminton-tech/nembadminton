<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePlayerId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = auth()->user();

        if (!$user || !$user->clubhouse_id) {
            return;
        }

        $exists = User::query()
            ->where('player_id', $value)
            ->where('clubhouse_id', $user->clubhouse_id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($exists !== null) {
            $fail('Denne spiller er allerede tilknyttet brugeren "'.$exists->name.'" i dit klubhus.');
        }
    }
}
