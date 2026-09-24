<?php

declare(strict_types=1);

namespace FlyCompany\Import;

/**
 * Class Club
 */
class Club
{
    private ?int $id = null;

    private ?string $name1 = null;

    private ?string $name2 = null;

    private ?string $address = null;

    private ?int $zipCode = null;

    private ?string $city = null;

    private ?string $email = null;

    private ?string $memberOf = null;

    private ?string $union = null;

    private MemberCollection $members;

    public static function isClubId(\SimpleXMLElement $attributes, int $clubId): bool
    {
        foreach ($attributes as $key => $value) {
            if ($key === 'id' && (int) $value === $clubId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  \SimpleXMLElement[]  $members
     */
    public static function xmlFactory(\SimpleXMLElement $attributes, ?\SimpleXMLElement $members): Club
    {
        $club = new self;
        foreach ($attributes as $key => $value) {
            if ($key === 'id') {
                $club->id = (int) $value;
            }
            if ($key === 'nm1') {
                $club->name1 = (string) $value;
            }
            if ($key === 'nm2') {
                $club->name2 = (string) $value;
            }
            if ($key === 'adr') {
                $club->address = (string) $value;
            }
            if ($key === 'zip') {
                $club->zipCode = (int) $value;
            }
            if ($key === 'city') {
                $club->city = (string) $value;
            }
            if ($key === 'email') {
                $club->email = (string) $value;
            }
            if ($key === 'medlemaf') {
                $club->memberOf = (string) $value;
            }
            if ($key === 'amstforening') {
                $club->union = (string) $value;
            }
        }

        if ($members !== null) {
            $membersCollection = new MemberCollection;
            foreach ($members as $member) {
                $membersCollection->add(Member::xmlFactory($member->attributes(), $member->g));
            }
            $club->members = $membersCollection;
        }

        return $club;
    }

    /**
     * @return MemberCollection|Member[]
     */
    public function getMembers(): MemberCollection
    {
        return $this->members;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName1(): ?string
    {
        return $this->name1;
    }

    public function getName2(): ?string
    {
        return $this->name2;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getZipCode(): ?int
    {
        return $this->zipCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getMemberOf(): ?string
    {
        return $this->memberOf;
    }

    public function getUnion(): ?string
    {
        return $this->union;
    }
}
