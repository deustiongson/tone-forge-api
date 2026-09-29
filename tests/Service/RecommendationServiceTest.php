<?php

namespace App\Tests\Service;

use App\Entity\Equipment;
use App\Entity\Guitar;
use App\Entity\ToneProfile;
use App\Enum\EquipmentType;
use App\Enum\Genre;
use App\Enum\PickupType;
use App\Exception\GuitarNotFoundException;
use App\Exception\ToneProfileNotFoundException;
use App\Repository\EquipmentRepository;
use App\Repository\GuitarRepository;
use App\Repository\ToneProfileRepository;
use App\Service\RecommendationService;
use PHPUnit\Framework\TestCase;

class RecommendationServiceTest extends TestCase 
{
    public function testRecommendThrowsExceptionWhenGuitarNotFound(): void
    {
        //create fake Guitar Repository
        $guitarRepository = $this->createStub(GuitarRepository::class);
        //Configure find() method to always return null
        $guitarRepository->method('find')->willReturn(null);

        //create fake Tone Profile and Equipment Repositories
        $toneProfileRepository = $this->createStub(ToneProfileRepository::class);
        $equipmentRepository = $this->createStub(EquipmentRepository::class);

        //Inject fake repositories into instance of RecommendationService
        $service = new RecommendationService($guitarRepository, $toneProfileRepository, $equipmentRepository);

        //For this test to pass, expect the next line to throw GuitarNotFoundException
        $this->expectException(GuitarNotFoundException::class);
        
        //Call the recommend method with an invalid guitar ID
        $service->recommend(9999, 1);
    }

    public function testRecommendThrowsExceptionWhenToneProfileNotFound(): void
    {   
        $testGuitar = new Guitar();

        $testGuitar->setName("Test Electric Guitar")
            ->setPickupType(PickupType::Humbucker)
            ->setTuning("Drop D");

        //create fake Guitar Repository
        $guitarRepository = $this->createStub(GuitarRepository::class);
        //Configure find() method to always return our testGuitar object
        $guitarRepository->method('find')->willReturn($testGuitar);

        //create fake Tone Profile Repository
        $toneProfileRepository = $this->createStub(ToneProfileRepository::class);
        //Configure find() method to always return null
        $toneProfileRepository->method('find')->willReturn(null);

        $equipmentRepository = $this->createStub(EquipmentRepository::class);

        //Inject fake repositories into instance of RecommendationService
        $service = new RecommendationService($guitarRepository, $toneProfileRepository, $equipmentRepository);

        //For this test to pass, expect the next line to throw ToneProfileNotFoundException
        $this->expectException(ToneProfileNotFoundException::class);
        
        //Call the recommend method with an invalid tone profile ID
        $service->recommend(1, 1);
    }

    public function testRecommendReturnsHighestScoringEquipmentPerType(): void
    {
        $guitar = new Guitar();
        $guitar->setName('Test Electric Guitar')
            ->setPickupType(PickupType::Humbucker)
            ->setTuning('E Flat Standard');
            
        $toneProfile = new ToneProfile();
        $toneProfile->setName('Test Rock Tone Profile')
            ->setGenre(Genre::Rock)
            ->setGain(5)    
            ->setBrightness(5);

        //Exact match with Genre, Gain, Brightness
        $bestAmp = new Equipment();
        $bestAmp->setName('Best Amp')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Rock)
            ->setGainRating(5)
            ->setBrightnessRating(5)
            ->setPreferredPickupType(null);

        //Genre is Jazz against Rock Tone Profile
        $worseAmp = new Equipment();
        $worseAmp->setName('Worse Amp')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Jazz)
            ->setGainRating(5)
            ->setBrightnessRating(5)
            ->setPreferredPickupType(null);

        //Same attribute values with Tone Profile
        $bestCabinet = new Equipment();
        $bestCabinet->setName('Best Cabinet')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Rock)
            ->setGainRating(5)
            ->setBrightnessRating(5)
            ->setPreferredPickupType(null);
        
        //Lower gain and brightness
        $worseCabinet = new Equipment();
        $worseCabinet->setName('Worse Cabinet')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Rock)
            ->setGainRating(3)
            ->setBrightnessRating(3)
            ->setPreferredPickupType(null);
        
        //matches the Rock Tone Profile
        $bestEffect = new Equipment();
        $bestEffect->setName('Best Effect')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Rock)
            ->setGainRating(5)
            ->setBrightnessRating(5)
            ->setPreferredPickupType(null);
        
        //Slightly lower Gain and Brightness
        $tiedEffect1 = new Equipment();
        $tiedEffect1->setName('Tied Effect 1')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Rock)
            ->setGainRating(4)
            ->setBrightnessRating(4)
            ->setPreferredPickupType(null);

        //Same values with $tiedEffect1
        $tiedEffect2 = new Equipment();
        $tiedEffect2->setName('Tied Effect 2')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Rock)
            ->setGainRating(4)
            ->setBrightnessRating(4)
            ->setPreferredPickupType(null);

        //Genre is Jazz and Gain and Brightness are lower than Rock Tone Profile
        $worstEffect = new Equipment();
        $worstEffect->setName('Worst Effect')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Jazz)
            ->setGainRating(4)
            ->setBrightnessRating(4)
            ->setPreferredPickupType(null);
        
        $allEquipment = [$bestAmp, $worseAmp, $bestCabinet, $worseCabinet, $bestEffect, $tiedEffect1, $tiedEffect2, $worstEffect];
        
        $guitarRepository = $this->createStub(GuitarRepository::class);
        $guitarRepository->method('find')->willReturn($guitar);

        $toneProfileRepository = $this->createStub(ToneProfileRepository::class);
        $toneProfileRepository->method('find')->willReturn($toneProfile);

        $equipmentRepository = $this->createStub(EquipmentRepository::class);
        $equipmentRepository->method('findAll')->willReturn($allEquipment);

        $service = new RecommendationService($guitarRepository, $toneProfileRepository, $equipmentRepository);

        $result = $service->recommend(1, 1);
        
        //The amplifier in the result should be the $bestAmp
        $this->assertSame($bestAmp, $result->amplifier);

        //The cabinet in the result should be the $bestCabinet 
        $this->assertSame($bestCabinet, $result->cabinet);

        //The list of effects in the result should be $bestEffect, $tiedEffect1, $tiedEfect2 
        $this->assertSame([$bestEffect, $tiedEffect1, $tiedEffect2], $result->effects);
    }
}