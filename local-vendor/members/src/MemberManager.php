<?php
declare(strict_types = 1);


namespace FlyCompany\Members;

use App\Models\Member;
use Illuminate\Support\Facades\Log;

class MemberManager
{

    /**
     * @param string      $refId
     * @param string      $name
     * @param string|null $gender
     *
     * @return Member
     */
    public function addOrUpdateMember(string $refId, string $name, ?string $gender) : Member
    {

        $memberModel = \App\Models\Member::query()->where('refId', $refId)->first();
        if ($memberModel !== null) {
            $memberModel->update([
                'name'   => $name,
                'gender' => $gender,
            ]);
        } else {
            $memberModel = \App\Models\Member::create([
                'refId'  => $refId,
                'name'   => $name,
                'gender' => $gender,
            ]);
        }

        return $memberModel;
    }

}
