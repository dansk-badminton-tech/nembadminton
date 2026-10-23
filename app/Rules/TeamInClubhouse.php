<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Team;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TeamInClubhouse implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Disconnecting is allowed; the Paper Squad invariant is enforced by Squad itself.
        if ($value === null) {
            return;
        }
        $user = auth()->user();
        if ($user === null || ! Team::query()->where('clubhouse_id', $user->clubhouse_id)->whereKey($value)->exists()) {
            $fail('Vælg et hold fra dit eget klubhus.');
        }
    }
}
