<?php

namespace App\Entity;

use App\Repository\SearchRepository;
use App\Services\AudioFetcher\Resources\Reciter;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SearchRepository::class, readOnly: true)]
class Search
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reciter $reciter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReciter(): ?Reciter
    {
        return $this->reciter;
    }

    public function setReciter(?Reciter $reciter): self
    {
        $this->reciter = $reciter;

        return $this;
    }

    public function __toString(): string
    {
        return 'test';
    }
}
