<?php


namespace App\Import;

use App\Console\Commands\XMLHelper;
use App\Models\Point;
use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class Import
{

    /**
     * @param string $date
     * @param array  $clubIds
     */
    public static function importMembers(string $date, array $clubIds) : void
    {
        $path = Path::getRankingPath($date);
        $data = XMLHelper::loadXML($path, Storage::disk());

        foreach ($clubIds as $clubId) {
            $ranking = Ranking::factoryClub($data, (int)$clubId);
            foreach ($ranking->getClubs() as $club) {
                if (in_array($club->getId(), $clubIds)) {
                    try {
                        if (!is_numeric($club->getId())) {
                            continue;
                        }
                        /** @var \App\Models\Club $clubModel */
                        $clubModel = \App\Models\Club::query()->where(['id' => $club->getId()])->firstOrFail();
                        $syncIds = [];
                        foreach ($club->getMembers() as $member) {
                            $memberModel = \App\Models\Member::query()->where('refId', $member->getId())->first();
                            if ($memberModel !== null) {
                                $memberModel->update([
                                    'name'     => $member->getName(),
                                    'gender'   => $member->getGender(),
                                    'birthday' => $member->getBirthday(),
                                ]);
                            } else {
                                $memberModel = \App\Models\Member::create([
                                    'refId'    => $member->getId(),
                                    'name'     => $member->getName(),
                                    'gender'   => $member->getGender(),
                                    'birthday' => $member->getBirthday(),
                                ]);
                            }

                            // Attaching points to member
                            foreach ($member->getMemberVintages() as $memberVintage) {
                                foreach ($memberVintage->getPoints() as $point) {
                                    Point::query()->create([
                                        'points'    => $point->getPoints(),
                                        'position'  => $point->getPosition(),
                                        'category'  => $point->getCategory(),
                                        'cll'       => $point->getCll(),
                                        'clh'       => $point->getClh(),
                                        'vintage'   => $memberVintage->getName(),
                                        'member_id' => $memberModel->id,
                                    ]);
                                }
                            }
                            $syncIds[] = $memberModel->id;
                        }
                        $clubModel->members()->sync($syncIds);
                    } catch (ModelNotFoundException $exception) {

                    }
                }
            }
        }
    }

}
