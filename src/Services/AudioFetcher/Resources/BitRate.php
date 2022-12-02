<?php

namespace App\Services\AudioFetcher\Resources;


class BitRate
{
    private ?string $name = null;

    private ?array $reciters;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getReciters()
    {
        return $this->reciters;
    }

    public function setReciters($reciters)
    {
        $this->reciters = $reciters;

        return $this;
    }
}
