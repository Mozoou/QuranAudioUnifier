<?php

namespace App\Services\AudioFetcher\Resources;

class Reciter
{
    private ?string $name = null;

    private ?string $subfolder = null;

    private ?BitRate $bitrate = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSubfolder(): ?string
    {
        return $this->subfolder;
    }

    public function setSubfolder(string $subfolder): self
    {
        $this->subfolder = $subfolder;

        return $this;
    }

    public function getBitrate(): ?BitRate
    {
        return $this->bitrate;
    }

    public function setBitrate(?BitRate $bitrate): self
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
