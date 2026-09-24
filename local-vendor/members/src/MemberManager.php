<?php

declare(strict_types=1);

namespace FlyCompany\Members;

use App\Models\Member;
use Illuminate\Support\Facades\Log;

class MemberManager
{
    public function addOrUpdateMember(string $refId, string $name, ?string $gender, bool $active = true): Member
    {

        $memberModel = Member::query()->where('refId', $refId)->first();
        if ($memberModel !== null) {
            $updateData = [
                'name' => $name,
                'gender' => $gender,
            ];
            if ($memberModel->override_inactive === 'AUTO') {
                $updateData['inactive'] = ! $active;
            }
            $memberModel->update($updateData);
        } else {
            $memberModel = Member::create([
                'refId' => $refId,
                'name' => $name,
                'gender' => $gender,
                'inactive' => ! $active,
                'override_inactive' => 'AUTO',
            ]);
        }

        return $memberModel;
    }
}
