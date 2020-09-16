<?php
declare(strict_types = 1);

namespace FlyCompany\Import;

class Member
{

    private ?string $id = null;
    private string $name = '';
    private ?string $gender = null;
    private ?string $birthday = null;
    private MemberVintageCollection $memberVintages;

    public static function xmlFactory(\SimpleXMLElement $attributes, \SimpleXMLElement $memberVintages) : Member
    {
        $member = new self();
        foreach ($attributes as $key => $value){
            if($key === 'id'){
                $member->id = (string)$value;
            }
            if($key === 'nam'){
                $member->name = (string)$value;
            }
            if($key === 'gen'){
                $member->gender = (string)$value;
            }
            if($key === 'dat'){
                $member->birthday = (string)$value;
            }
        }

        $vintageCollection = new MemberVintageCollection();
        foreach ($memberVintages as $memberVintage){
            $vintageCollection->add(MemberVintage::xmlFactory($memberVintage->attributes(), $memberVintage->r));
        }
        $member->memberVintages = $vintageCollection;

        return $member;
    }

    /**
     * @return MemberVintageCollection|MemberVintage[]
     */
    public function getMemberVintages() : MemberVintageCollection
    {
        return $this->memberVintages;
    }

    /**
     * @return string|null
     */
    public function getId() : ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getGender() : ?string
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getBirthday() : ?string
    {
        return $this->birthday;
    }

}
