<?php

namespace App\Dto;
use App\Entity\Equipment;
use App\Entity\Guitar;
use App\Entity\ToneProfile;

class RecommendationResult 
{   
    /**
     * @param Equipment[] $effects
     */
    public function __construct(
        public readonly Guitar $guitar,
        public readonly ToneProfile $toneProfile,
        public readonly ?Equipment $amplifier,
        public readonly ?Equipment $cabinet,
        public readonly array $effects,
    ) {

    }
}