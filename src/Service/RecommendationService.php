<?php

namespace App\Service;

use App\Dto\RecommendationResult;
use App\Entity\Equipment;
use App\Entity\Guitar;
use App\Entity\ToneProfile;
use App\Exception\GuitarNotFoundException;
use App\Exception\ToneProfileNotFoundException;
use App\Repository\EquipmentRepository;
use App\Repository\GuitarRepository;
use App\Repository\ToneProfileRepository;
use App\Enum\EquipmentType;

class RecommendationService {
    private const EFFECT_COUNT = 2;

    public function __construct(
        private readonly GuitarRepository $guitarRepository,
        private readonly ToneProfileRepository $toneProfileRepository,
        private readonly EquipmentRepository $equipmentRepository,
    ) {
    }

    public function recommend(int $guitarId, int $toneProfileId): RecommendationResult
    {
        $guitar = $this->guitarRepository->find($guitarId);

        if($guitar == null) {
            throw new GuitarNotFoundException("Guitar with id {$guitarId} not found.");
        }

        $toneProfile = $this->toneProfileRepository->find($toneProfileId);

        if($toneProfile == null) {
            throw new ToneProfileNotFoundException("Tone Profile with id {$toneProfileId} not found.");
        }

        $amplifier = null;
        $amplifierScore = -1;

        $cabinet = null;
        $cabinetScore = -1;

        $scoredEffects = [];

        $allEquipment = $this->equipmentRepository->findAll();

        foreach($allEquipment as $equipment) {
            $currentScore = $this->score($equipment, $guitar, $toneProfile);

            $currentType = $equipment->getType();
            
            // If the current equipment is an Amplifier and its score is greater than amplifierScore,
            // Set it as the amplifier and its score as the amplifierScore
            if($currentType === EquipmentType::Amplifier && $currentScore > $amplifierScore) {
                $amplifier = $equipment;
                $amplifierScore = $currentScore;
            }
            // If the current equipment is a Cabinet and its score is greater than cabinetScore,
            // Set it as the cabinet and its score as the cabinetScore
            else if ($currentType === EquipmentType::Cabinet && $currentScore > $cabinetScore ) {
                $cabinet = $equipment;
                $cabinetScore = $currentScore;
            }
            // If the current equipment is an Effect, push it to the scoredEffects array
            else if ($currentType == EquipmentType::Effect) {
                $scoredEffects[] = ['equipment' => $equipment, 'score' => $currentScore];
            }
        }

        //Get the top effects from the scoredEffects array
        $topEffects = $this->pickTopEffects($scoredEffects);
        return new RecommendationResult($guitar, $toneProfile, $amplifier, $cabinet, $topEffects);
    }

    private function score(Equipment $equipment, Guitar $guitar, ToneProfile $toneProfile): int
    {
        $score = 0;

        $equipGenre = $equipment->getGenre();
        $toneProfGenre = $toneProfile->getGenre();
        $equipGainRating = $equipment->getGainRating();
        $toneProfGain = $toneProfile->getGain();
        $equipBrightness = $equipment->getBrightnessRating();
        $toneProfBrightness = $toneProfile->getBrightness();
        $equipPrefPickup = $equipment->getPreferredPickupType();
        $guitPickup = $guitar->getPickupType();
        
        // If the equipment genre and the tone profile genre are the same, add 5 to the score
        if($equipGenre === $toneProfGenre) {
            $score += 5;
        }
        
        $score += (10 - abs($equipGainRating - $toneProfGain)) * 2;
        $score += (10 - abs($equipBrightness - $toneProfBrightness)) * 2;

        // If the preferred pickup type of the equipment is the same as the guitar pickup, add 1 to the score
        if ($equipPrefPickup === $guitPickup) {
            $score += 1;
        }

        return $score;
    }

    private function pickTopEffects(array $scoredEffects): array
    {   
        //Sort $scoredEffects in descending order according to score
        usort($scoredEffects, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        //if the number of scored effects is less than or equal to EFFECT_COUNT (2), just use the array
        if (count($scoredEffects) <= self::EFFECT_COUNT) {
            return array_column($scoredEffects, 'equipment');
        }

        //Get the cutoff score by getting the score of the (2-1) + 1th effect
        $cutoffScore = $scoredEffects[self::EFFECT_COUNT - 1]['score'];

        //Get only the effects that have scores >= than the cutoff score
        //return the equipment(name) column of the resulting array only
        return array_column(
            array_filter($scoredEffects, fn (array $entry) => $entry['score'] >= $cutoffScore),
            'equipment'
        );
    }
}