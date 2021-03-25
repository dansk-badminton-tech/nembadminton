<?php
declare(strict_types = 1);


namespace FlyCompany\Members;

use App\Models\Member;

class MemberManager
{

    /**
     * @param string      $refId
     * @param string      $name
     * @param string|null $gender
     *
     * @return Member
     */
    public function addMember(string $refId, string $name, ?string $gender)
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
