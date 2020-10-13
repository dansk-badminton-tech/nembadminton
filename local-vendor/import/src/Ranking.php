<?php
declare(strict_types = 1);

namespace FlyCompany\Import;

use Illuminate\Support\Str;

class Ranking
{

    private string $version;
    private string $format;
    private string $time;

    /**
     * @var VintageCollection
     */
    private VintageCollection $vintageCollection;

    /**
     * @var LeagueCollection
     */
    private LeagueCollection $leagues;

    /**
     * @var ClubCollection
     */
    private ClubCollection $clubs;

    public function __construct(VintageCollection $vintages, LeagueCollection $leagues, ClubCollection $clubs)
    {
        $this->vintageCollection = $vintages;
        $this->leagues = $leagues;
        $this->clubs = $clubs;
    }

    public static function factoryClub(\SimpleXMLElement $data, int $id)
    {
        $leagueCollection = new LeagueCollection();
        $vintageCollection = new VintageCollection();
        $clubCollection = new ClubCollection();
        foreach ($data->c as $club) {
            if (Club::isClubId($club->attributes(), $id)) {
                $clubCollection->add(Club::xmlFactory($club->attributes(), $club));
            }
        }

        return self::createRanking($vintageCollection, $leagueCollection, $clubCollection, $data);
    }

    public static function factory(\SimpleXMLElement $data)
    {
        $vintageCollection = new VintageCollection();
        foreach ($data->gl->g as $vintage) {
            $vintageCollection->add(Vintage::xmlFactory($vintage->attributes()));
        }
        $leagueCollection = new LeagueCollection();
        foreach ($data->cl->c as $league) {
            $leagueCollection->add(League::xmlFactory($league->attributes()));
        }
        $clubCollection = new ClubCollection();
        foreach ($data->c as $club) {
            $clubCollection->add(Club::xmlFactory($club->attributes(), $club));
        }

        return self::createRanking($vintageCollection, $leagueCollection, $clubCollection, $data);
    }

    /**
     * @return VintageCollection
     */
    public function getVintageCollection() : VintageCollection
    {
        return $this->vintageCollection;
    }

    /**
     * @return LeagueCollection
     */
    public function getLeagues() : LeagueCollection
    {
        return $this->leagues;
    }

    /**
     * @return ClubCollection|Club[]
     */
    public function getClubs() : ClubCollection
    {
        return $this->clubs;
    }

    /**
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getFormat() : string
    {
        return $this->format;
    }

    /**
     * @return string
     */
    public function getTime() : string
    {
        return $this->time;
    }

    public function searchMemberByName(string $name) : MemberCollection{
        $members = new MemberCollection();
        /** @var Club $club */
        foreach ($this->clubs as $club){
            $filteredMembers = $club->getMembers()->filter(static function (Member $value) use ($name) {
                return Str::contains($value->getName(), $name);
            });
            $members = $members->merge($filteredMembers);
        }

        return $members;
    }

    public function searchClubByName(string $name)
    {
        return $this->clubs->filter(static function (Club $value) use ($name) {
            return Str::contains($value->getName1(), $name);
        });
    }

    /**
     * @param VintageCollection $vintageCollection
     * @param LeagueCollection  $leagueCollection
     * @param ClubCollection    $clubCollection
     * @param \SimpleXMLElement $data
     *
     * @return Ranking
     */
    protected static function createRanking(VintageCollection $vintageCollection, LeagueCollection $leagueCollection, ClubCollection $clubCollection, \SimpleXMLElement $data) : Ranking
    {
        $ranking = new self($vintageCollection, $leagueCollection, $clubCollection);
        foreach ($data->attributes() as $key => $value) {
            if ($key === 'version') {
                $ranking->version = (string)$value;
            }
            if ($key === 'format') {
                $ranking->format = (string)$value;
            }
            if ($key === 'time') {
                $ranking->time = (string)$value;
            }
        }

        return $ranking;
    }

}
