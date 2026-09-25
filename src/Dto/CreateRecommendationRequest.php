<?php

namespace App\Dto;
use Symfony\Component\Validator\Constraints as Assert;

class CreateRecommendationRequest {

    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $guitarId = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $toneProfileId = null;
}