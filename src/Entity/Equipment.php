<?php

namespace App\Entity;

use App\Enum\EquipmentType;
use App\Enum\Genre;
use App\Enum\PickupType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(enumType: EquipmentType::class)]
    private EquipmentType $type;

    #[ORM\Column(enumType: Genre::class)]
    private Genre $genre;

    #[ORM\Column]
    private int $gainRating;

    #[ORM\Column]
    private int $brightnessRating;

    #[ORM\Column(enumType: PickupType::class, nullable: true)]
    private ?PickupType $preferredPickupType = null;

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

    public function getType(): EquipmentType
    {
        return $this->type;
    }

    public function setType(EquipmentType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getGenre(): Genre
    {
        return $this->genre;
    }

    public function setGenre(Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getGainRating(): int
    {
        return $this->gainRating;
    }

    public function setGainRating(int $gainRating): static
    {
        $this->gainRating = $gainRating;
        return $this; 
    }

    public function getBrightnessRating(): int
    {
        return $this->brightnessRating;
    }

    public function setBrightnessRating(int $brightnessRating): static
    {
        $this->brightnessRating = $brightnessRating;
        return $this; 
    }

    public function getPreferredPickupType(): ?PickupType
    {
        return $this->preferredPickupType;
    }

    public function setPreferredPickupType(?PickupType $preferredPickupType): static
    {
        $this->preferredPickupType = $preferredPickupType;

        return $this;
    }
}