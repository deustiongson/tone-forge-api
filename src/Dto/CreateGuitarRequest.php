<?php

namespace App\Dto;

use App\Enum\PickupType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateGuitarRequest
{   
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotNull]
    public ?PickupType $pickupType = null;

    #[Assert\NotBlank]
    public ?string $tuning = null;
}