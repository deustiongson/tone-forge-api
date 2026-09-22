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

        $manager->flush();
    }
}
