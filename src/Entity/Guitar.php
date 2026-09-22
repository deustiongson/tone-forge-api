<?php

namespace App\Entity;

use App\Enum\PickupType;
use App\Repository\GuitarRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuitarRepository::class)]
class Guitar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(enumType: PickupType::class)]
    private PickupType $pickupType;

    #[ORM\Column(length: 50)]
    private string $tuning;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPickupType(): PickupType
    {
        return $this->pickupType;
    }

    public function setPickupType(PickupType $pickupType): static
    {
        $this->pickupType = $pickupType;

        return $this;
    }

    public function getTuning(): string
    {
        return $this->tuning;
    }

    public function setTuning(string $tuning): static
    {
        $this->tuning = $tuning;

        return $this;
    }
}