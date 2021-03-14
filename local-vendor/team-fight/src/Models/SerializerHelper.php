<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight\Models;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerHelper
{

    /**
     * @return Serializer
     */
    public static function getSerializer() : Serializer
    {
        $phpDocExtractor = new PhpDocExtractor();
        $typeExtractors = [$phpDocExtractor];
        $propertyInfo = new PropertyInfoExtractor([], $typeExtractors);

        $encoders = [new JsonEncoder()];
        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer(null, null, null, $propertyInfo)];

        return new Serializer($normalizers, $encoders);
    }

}
