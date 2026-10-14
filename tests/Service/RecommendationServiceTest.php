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
        
        $testGuitar = $this->buildGuitar(PickupType::Humbucker, 'Drop D');
        
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
        $guitar = $this->buildGuitar(PickupType::Humbucker, 'E Flat Standard');
        
        $toneProfile = $this->buildToneProfile(Genre::Rock, 5, 5);

        //Exact match with Genre, Gain, Brightness
        $bestAmp = $this->buildEquipment('Best Amp', EquipmentType::Amplifier, Genre::Rock, 5, 5, null);

        //Genre is Jazz against Rock Tone Profile
        $worseAmp = $this->buildEquipment('Worse Amp', EquipmentType::Amplifier, Genre::Jazz, 5, 5, null);
        
        //Same attribute values with Tone Profile
        $bestCabinet = $this->buildEquipment('Best Cabinet', EquipmentType::Cabinet, Genre::Rock, 5, 5, null);
        
        //Lower gain and brightness
        $worseCabinet = $this->buildEquipment('Worse Cabinet', EquipmentType::Cabinet, Genre::Rock, 3, 3, null);
        
        //matches the Rock Tone Profile
        $bestEffect = $this->buildEquipment('Best Effect', EquipmentType::Effect, Genre::Rock, 5, 5, null);
        
        //Slightly lower Gain and Brightness
        $tiedEffect1 = $this->buildEquipment('Tied Effect 1', EquipmentType::Effect, Genre::Rock, 4, 4, null);

        //Same values with $tiedEffect1
        $tiedEffect2 = $this->buildEquipment('Tied Effect 2', EquipmentType::Effect, Genre::Rock, 4, 4, null);

        //Genre is Jazz and Gain and Brightness are lower than Rock Tone Profile
        $worstEffect = $this->buildEquipment('Worst Effect', EquipmentType::Effect, Genre::Jazz, 4, 4, null);
        
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

    public function testRecommendReturnsEmptyEquipmentWithEmptyCatalog()
    {
        $guitar = $this->buildGuitar(PickupType::P90, 'E Standard');
            
        $toneProfile = $this->buildToneProfile(Genre::Jazz, 3, 4);

        $guitarRepository = $this->createStub(GuitarRepository::class);
        $guitarRepository->method('find')->willReturn($guitar);

        $toneProfileRepository = $this->createStub(ToneProfileRepository::class);
        $toneProfileRepository->method('find')->willReturn($toneProfile);

        //findAll will return empty array (empty equipment catalog)
        $equipmentRepository = $this->createStub(EquipmentRepository::class);
        $equipmentRepository->method('findAll')->willReturn([]);

        $service = new RecommendationService($guitarRepository, $toneProfileRepository, $equipmentRepository);

        $result = $service->recommend(1, 1);

        //no amplifier and cabinet, empty effects list
        $this->assertNull($result->amplifier);
        $this->assertNull($result->cabinet);
        $this->assertSame([], $result->effects);
    }

    public function testRecommendReturnsFewerEquipmentWithSmallCatalog()
    {   
        $guitar = $this->buildGuitar(PickupType::Humbucker, 'D Standard');
        
        $toneProfile = $this->buildToneProfile(Genre::Blues, 4, 5);
        
        $effect = $this->buildEquipment('Test Effect', EquipmentType::Effect, Genre::Blues, 5, 5, null);
        
        $guitarRepository = $this->createStub(GuitarRepository::class);
        $guitarRepository->method('find')->willReturn($guitar);

        $toneProfileRepository = $this->createStub(ToneProfileRepository::class);
        $toneProfileRepository->method('find')->willReturn($toneProfile);

        //findAll will return one effect
        $equipmentRepository = $this->createStub(EquipmentRepository::class);
        $equipmentRepository->method('findAll')->willReturn([$effect]);

        $service = new RecommendationService($guitarRepository, $toneProfileRepository, $equipmentRepository);

        $result = $service->recommend(1, 1);
        $this->assertSame([$effect], $result->effects);
    }

    //Mocks (behavior/interaction verification)
    public function testRecommendCallsFindWithGuitarIdAndToneProfileId(): void
    {
        $guitar = $this->buildGuitar();
        $toneProfile = $this->buildToneProfile();

        $guitarRepository = $this->createMock(GuitarRepository::class);
        $guitarRepository->expects($this->once())
            ->method('find')
            ->with(15)
            ->willReturn($guitar);

        $toneProfileRepository = $this->createMock(ToneProfileRepository::class);
        $toneProfileRepository->expects($this->once())
            ->method('find')
            ->with(58)
            ->willReturn($toneProfile);

        $equipmentRepository = $this->createStub(EquipmentRepository::class);
        $equipmentRepository->method('findAll')->willReturn([]);

        $service = new RecommendationService($guitarRepository, $toneProfileRepository, $equipmentRepository);
        $service->recommend(15, 58);
    }

    public function testRecommendReturnsMorePickupCompatibleWithIdenticalEquipment()
    {
        $guitar = $this->buildGuitar(PickupType::Humbucker, 'E Standard');

        $toneProfile = $this->buildToneProfile(Genre::Jazz, 5, 5);

        $matchingPickupAmp = $this->buildEquipment('Matching Pickup Amp', EquipmentType::Amplifier, Genre::Jazz, 5, 5, PickupType::Humbucker);
        $nonMatchingPickupAmp = $this->buildEquipment('Non-Matching Pickup Amp', EquipmentType::Amplifier, Genre::Jazz, 5, 5, null);

        $guitarRepository = $this->createStub(GuitarRepository::class);
        $guitarRepository->method('find')->willReturn($guitar);

        $toneProfileRepository = $this->createStub(ToneProfileRepository::class);
        $toneProfileRepository->method('find')->willReturn($toneProfile);

        $equipmentRepository = $this->createStub(EquipmentRepository::class);
        $equipmentRepository->method('findAll')->willReturn([$nonMatchingPickupAmp, $matchingPickupAmp]);

        $service = new RecommendationService($guitarRepository, $toneProfileRepository, $equipmentRepository);

        $result = $service->recommend(1, 1);

        $this->assertSame($matchingPickupAmp, $result->amplifier);
    }

    private function buildGuitar(PickupType $pickupType = PickupType::Humbucker, string $tuning = 'E Standard'): Guitar 
    {   
        $guitar = new Guitar();
        $guitar->setName("Test Electric Guitar")
            ->setPickupType($pickupType)
            ->setTuning($tuning);

        return $guitar;
    }

    private function buildToneProfile(Genre $genre = Genre::Jazz, int $gain = 5, int $brightness = 5): ToneProfile
    {
        $toneProfile = new ToneProfile();
        $toneProfile->setName('Test Jazz Tone Profile')
            ->setGenre($genre)
            ->setGain($gain)    
            ->setBrightness($brightness);

        return $toneProfile;
    }

    private function buildEquipment(string $name, EquipmentType $equipmentType, Genre $genre, int $gain, int $brightness, ?PickupType $preferredPickupType = null): Equipment
    {
        $equipment = new Equipment();
        $equipment->setName($name)
            ->setType($equipmentType)
            ->setGenre($genre)
            ->setGainRating($gain)
            ->setBrightnessRating($brightness)
            ->setPreferredPickupType($preferredPickupType);

        return $equipment;
    }
}