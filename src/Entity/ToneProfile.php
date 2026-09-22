<?php

namespace App\Entity;

use App\Enum\Genre;
use App\Repository\ToneProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToneProfileRepository::class)]
class ToneProfile 
{   
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(enumType: Genre::class)]
    private Genre $genre;

    #[ORM\Column]
    private int $gain;

    #[ORM\Column]
    private int $brightness;

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

    public function getGenre(): Genre
    {
        return $this->genre;
    }

    public function setGenre(Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getGain(): int
    {
        return $this->gain;
    }

    public function setGain(int $gain): static
    {
        $this->gain = $gain;
        return $this;
    }

    public function getBrightness(): int
    {
        return $this->brightness;
    }

    public function setBrightness(int $brightness): static
    {
        $this->brightness = $brightness;
        return $this;
    }
}