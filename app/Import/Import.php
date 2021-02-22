<?php
declare(strict_types = 1);

namespace App\Import;

use App\Console\Commands\XMLHelper;
use App\Models\Point;
use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Psr\Log\LoggerInterface;

class Import
{

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $output;

    public function __construct(LoggerInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string $date
     * @param array  $clubIds
     */
    public function importMembers(string $date, array $clubIds) : void
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
                        $this->output->info('Adding members to ' . $club->getName1());
                        /** @var \App\Models\Club $clubModel */
                        $clubModel = \App\Models\Club::query()->where(['id' => $club->getId()])->firstOrFail();
                        $syncIds = [];
                        foreach ($club->getMembers() as $member) {
                            $memberModel = \App\Models\Member::query()->where('refId', $member->getId())->first();
                            if ($memberModel !== null) {
                                $this->output->info('Updating ' . $member->getName());
                                $memberModel->update([
                                    'name'     => $member->getName(),
                                    'gender'   => $member->getGender(),
                                    'birthday' => $member->getBirthday(),
                                ]);
                            } else {
                                $this->output->info('Creating ' . $member->getName());
                                $memberModel = \App\Models\Member::create([
                                    'refId'    => $member->getId(),
                                    'name'     => $member->getName(),
                                    'gender'   => $member->getGender(),
                                    'birthday' => $member->getBirthday(),
                                ]);
                            }

                            $this->output->info('Removing old points');
                            Point::query()->where('member_id', $memberModel->id)->where('version', $date)->delete();
                            foreach ($member->getMemberVintages() as $memberVintage) {
                                foreach ($memberVintage->getPoints() as $point) {
                                    $this->output->info('Adding points for ' . $point->getCategory());
                                    if ($this->validPoint($point)) {
                                        Point::query()->create([
                                            'points'    => $point->getPoints(),
                                            'position'  => $point->getPosition(),
                                            'category'  => $point->getCategory(),
                                            'cll'       => $point->getCll(),
                                            'clh'       => $point->getClh(),
                                            'vintage'   => $memberVintage->getName(),
                                            'version'   => $date,
                                            'member_id' => $memberModel->id,
                                        ]);
                                    }
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

    /**
     * @param string $date
     * @param array  $clubIds
     */
    public function importClubs(string $date, array $clubIds) : void
    {
        $path = Path::getRankingPath($date);
        $this->output->info('Loading ' . $path);
        $data = XMLHelper::loadXML($path, Storage::disk());
        $this->output->info('Mapping to objects');
        $ranking = Ranking::factoryClubWithoutMembers($data);

        foreach ($ranking->getClubs() as $club) {
            if (in_array($club->getId(), $clubIds) || empty($clubIds)) {
                $this->output->info('Updating ' . $club->getName1());
                if (!is_numeric($club->getId())) {
                    continue;
                }
                \App\Models\Club::updateOrCreate([
                    'id' => $club->getId(),
                ], [
                    'name1'    => $club->getName1(),
                    'name2'    => $club->getName2(),
                    'address'  => $club->getAddress(),
                    'zipCode'  => $club->getZipCode(),
                    'city'     => $club->getCity(),
                    'email'    => $club->getEmail(),
                    'memberOf' => $club->getMemberOf(),
                    'union'    => $club->getUnion(),
                ]);
            }
        }
    }

    private function validPoint(\FlyCompany\Import\Point $point)
    {
        return !($point->getPoints() === null && $point->getPosition() === null && $point->getCategory() === null);
    }

}
