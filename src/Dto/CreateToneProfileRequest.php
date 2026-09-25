<?php

namespace App\Dto;

use App\Enum\Genre;
use Symfony\Component\Validator\Constraints as Assert;

class CreateToneProfileRequest
{   
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotNull]
    public ?Genre $genre = null;

    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 10)]
    public ?int $gain = null;

    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 10)]
    public ?int $brightness = null;

}