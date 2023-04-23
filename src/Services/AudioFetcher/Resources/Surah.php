<?php

namespace App\Services\AudioFetcher\Resources;

class Surah
{
    private ?int $number = null;

    private ?string $name = null;

    private ?string $englishName = null;

    private ?string $englishNameTranslation = null;

    private ?int $numberOfAyahs = null;

    private ?string $revelationType = null;

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getEnglishName()
    {
        return $this->englishName;
    }

    public function setEnglishName($englishName)
    {
        $this->englishName = $englishName;

        return $this;
    }

    public function getEnglishNameTranslation()
    {
        return $this->englishNameTranslation;
    }

    public function setEnglishNameTranslation($englishNameTranslation)
    {
        $this->englishNameTranslation = $englishNameTranslation;

        return $this;
    }

    public function getNumberOfAyahs()
    {
        return $this->numberOfAyahs;
    }

    public function setNumberOfAyahs($numberOfAyahs)
    {
        $this->numberOfAyahs = $numberOfAyahs;

        return $this;
    }

    public function getRevelationType()
    {
        return $this->revelationType;
    }

    public function setRevelationType($revelationType)
    {
        $this->revelationType = $revelationType;

        return $this;
    }
}
