<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\Equipment;
use App\Enum\EquipmentType;
use App\Enum\Genre;
use App\Enum\PickupType;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //Rock Equipment

        $marshallPlexi = new Equipment();
        $marshallPlexi->setName('Marshall Plexi')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Rock)
            ->setGainRating(8)
            ->setBrightnessRating(9)
            ->setPreferredPickupType(PickupType::Humbucker);

        $manager->persist($marshallPlexi);

        $celestV30412 = new Equipment();
        $celestV30412->setName('Celestion V30 4x12')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Rock)
            ->setGainRating(7)
            ->setBrightnessRating(8)
            ->setPreferredPickupType(null);

        $manager->persist($celestV30412);

        $ts9 = new Equipment();
        $ts9->setName('Ibanez TubeScreamer TS-9')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Rock)
            ->setGainRating(8)
            ->setBrightnessRating(6)
            ->setPreferredPickupType(null);

        $manager->persist($ts9);

        //Jazz Equipment

        $rolandJC120 = new Equipment();
        $rolandJC120->setName('Roland Jazz Chorus 120')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Jazz)
            ->setGainRating(4)
            ->setBrightnessRating(6)
            ->setPreferredPickupType(null);

        $manager->persist($rolandJC120);

        $fender212 = new Equipment();
        $fender212->setName('Fender Openback 2x12')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Jazz)
            ->setGainRating(5)
            ->setBrightnessRating(6)
            ->setPreferredPickupType(null);

        $manager->persist($fender212);

        $dynaComp = new Equipment();
        $dynaComp->setName('MXR DynaComp Compressor')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Jazz)
            ->setGainRating(4)
            ->setBrightnessRating(5)
            ->setPreferredPickupType(null);

        $manager->persist($dynaComp);

        //Blues Equipment

        $fenderSuper = new Equipment();
        $fenderSuper->setName('Fender Super Reverb')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Blues)
            ->setGainRating(6)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);

        $manager->persist($fenderSuper);

        $fender412 = new Equipment();
        $fender412->setName('Fender Openback 4x12')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Blues)
            ->setGainRating(6)
            ->setBrightnessRating(8)
            ->setPreferredPickupType(null);

        $manager->persist($fender412);

        $bluesDriver = new Equipment();
        $bluesDriver->setName('Boss BD-2 Blues Driver')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Blues)
            ->setGainRating(6)
            ->setBrightnessRating(8)
            ->setPreferredPickupType(null);

        $manager->persist($bluesDriver);

        //Metal Equipment

        $revvGen120 = new Equipment();
        $revvGen120->setName('Revv Generator 120')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Metal)
            ->setGainRating(10)
            ->setBrightnessRating(8)
            ->setPreferredPickupType(null);

        $manager->persist($revvGen120);

        $zilla412 = new Equipment();
        $zilla412->setName('Zilla Cab 4x12')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Metal)
            ->setGainRating(9)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);

        $manager->persist($zilla412);

        $precisionDrive = new Equipment();
        $precisionDrive->setName('Horizon Devices Precision Drive')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Metal)
            ->setGainRating(10)
            ->setBrightnessRating(8)
            ->setPreferredPickupType(null);

        $manager->persist($precisionDrive);

        //Additional Rock Equipment
        $friedmanBE100 = new Equipment();
        $friedmanBE100->setName('Friedman BE-100')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Rock)
            ->setGainRating(8)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($friedmanBE100);

        $marshall1960A = new Equipment();
        $marshall1960A->setName('Marshall 1960A 4x12')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Rock)
            ->setGainRating(7)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($marshall1960A);

        $bossSD1 = new Equipment();
        $bossSD1->setName('Boss SD-1 Super Overdrive')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Rock)
            ->setGainRating(7)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($bossSD1);

        $mxrDistortionPlus = new Equipment();
        $mxrDistortionPlus->setName('MXR Distortion+')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Rock)
            ->setGainRating(7)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($mxrDistortionPlus);

        //Additional Jazz Equipment
        $fenderTwinReverb = new Equipment();
        $fenderTwinReverb->setName("Fender '65 Twin Reverb")
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Jazz)
            ->setGainRating(3)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($fenderTwinReverb);

        $fenderBluesDeluxeCab = new Equipment();
        $fenderBluesDeluxeCab->setName('Fender Blues Deluxe 1x12 (Jensen C12N)')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Jazz)
            ->setGainRating(4)
            ->setBrightnessRating(5)
            ->setPreferredPickupType(null);
        $manager->persist($fenderBluesDeluxeCab);

        $tcHallOfFame = new Equipment();
        $tcHallOfFame->setName('TC Electronic Hall of Fame Reverb')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Jazz)
            ->setGainRating(3)
            ->setBrightnessRating(6)
            ->setPreferredPickupType(null);
        $manager->persist($tcHallOfFame);

        $bossCE2 = new Equipment();
        $bossCE2->setName('Boss CE-2 Chorus')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Jazz)
            ->setGainRating(3)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($bossCE2);

        //Additional Blues Equipment
        $fenderDeluxeReverb = new Equipment();
        $fenderDeluxeReverb->setName('Fender Deluxe Reverb')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Blues)
            ->setGainRating(5)
            ->setBrightnessRating(6)
            ->setPreferredPickupType(null);
        $manager->persist($fenderDeluxeReverb);

        $fenderBluesJuniorCab = new Equipment();
        $fenderBluesJuniorCab->setName('Fender Blues Junior 1x12')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Blues)
            ->setGainRating(5)
            ->setBrightnessRating(6)
            ->setPreferredPickupType(null);
        $manager->persist($fenderBluesJuniorCab);

        $fulltoneOCD = new Equipment();
        $fulltoneOCD->setName('Fulltone OCD')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Blues)
            ->setGainRating(6)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($fulltoneOCD);

        $kingOfTone = new Equipment();
        $kingOfTone->setName('Analog Man King of Tone')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Blues)
            ->setGainRating(6)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($kingOfTone);

        //Additional Metal Equipment
        $evh5150 = new Equipment();
        $evh5150->setName('EVH 5150III')
            ->setType(EquipmentType::Amplifier)
            ->setGenre(Genre::Metal)
            ->setGainRating(9)
            ->setBrightnessRating(8)
            ->setPreferredPickupType(PickupType::Humbucker);
        $manager->persist($evh5150);

        $mesaRectifierCab = new Equipment();
        $mesaRectifierCab->setName('Mesa Boogie Rectifier 4x12')
            ->setType(EquipmentType::Cabinet)
            ->setGenre(Genre::Metal)
            ->setGainRating(9)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($mesaRectifierCab);

        $mxr5150Overdrive = new Equipment();
        $mxr5150Overdrive->setName('MXR 5150 Overdrive')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Metal)
            ->setGainRating(9)
            ->setBrightnessRating(8)
            ->setPreferredPickupType(null);
        $manager->persist($mxr5150Overdrive);

        $wamplerPinnacle = new Equipment();
        $wamplerPinnacle->setName('Wampler Pinnacle')
            ->setType(EquipmentType::Effect)
            ->setGenre(Genre::Metal)
            ->setGainRating(8)
            ->setBrightnessRating(7)
            ->setPreferredPickupType(null);
        $manager->persist($wamplerPinnacle);

        $manager->flush();
    }
}
