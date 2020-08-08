<?php
declare(strict_types = 1);

namespace FlyCompany\Import;

class Club
{

    private ?int    $id;

    private ?string $name1 = '';

    private ?string $name2;

    private ?string $address;

    private ?int    $zipCode;

    private ?string $city;

    private ?string $email;

    private ?string $memberOf;

    private ?string $union;

    private MemberCollection $members;

    /**
     * @param \SimpleXMLElement         $attributes
     * @param \SimpleXMLElement[] $members
     *
     * @return Club
     */
    public static function xmlFactory(\SimpleXMLElement $attributes, \SimpleXMLElement $members) : Club
    {
        $club = new self();
        foreach ($attributes as $key => $value) {
            if ($key === 'id') {
                $club->id = (int)$value;
            }
            if ($key === 'nm1') {
                $club->name1 = (string)$value;
            }
            if ($key === 'nm2') {
                $club->name2 = (string)$value;
            }
            if ($key === 'adr') {
                $club->address = (string)$value;
            }
            if ($key === 'zip') {
                $club->zipCode = (int)$value;
            }
            if ($key === 'city') {
                $club->city = (string)$value;
            }
            if ($key === 'email') {
                $club->email = (string)$value;
            }
            if ($key === 'medlemaf') {
                $club->memberOf = (string)$value;
            }
            if ($key === 'amstforening') {
                $club->union = (string)$value;
            }
        }

        $membersCollection = new MemberCollection();
        foreach ($members as $member){
            $membersCollection->add(Member::xmlFactory($member->attributes(), $member->g));
        }
        $club->members = $membersCollection;

        return $club;
    }

    /**
     * @return MemberCollection
     */
    public function getMembers() : MemberCollection
    {
        return $this->members;
    }

    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName1() : ?string
    {
        return $this->name1;
    }

    /**
     * @return string|null
     */
    public function getName2() : ?string
    {
        return $this->name2;
    }

    /**
     * @return string|null
     */
    public function getAddress() : ?string
    {
        return $this->address;
    }

    /**
     * @return int|null
     */
    public function getZipCode() : ?int
    {
        return $this->zipCode;
    }

    /**
     * @return string|null
     */
    public function getCity() : ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getMemberOf() : ?string
    {
        return $this->memberOf;
    }

    /**
     * @return string|null
     */
    public function getUnion() : ?string
    {
        return $this->union;
    }


}
