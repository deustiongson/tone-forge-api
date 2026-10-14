<?php

namespace App\Tests\Entity;

use App\Enum\EquipmentType;
use App\Enum\Genre;
use App\Enum\PickupType;
use App\Entity\Equipment;
use PHPUnit\Framework\TestCase;

class EquipmentTest extends TestCase
{
    public function testNameSetter(): void
    {
       $equipment = new Equipment();
       $name = 'Test Equipment';
       $equipment->setName($name);

       $this->assertSame($name, $equipment->getName());
    }

    public function testEquipmentTypeSetter(): void
    {
        $equipment = new Equipment();
        $type = EquipmentType::Effect;
        $equipment->setType($type);

        $this->assertSame($type, $equipment->getType());
    }

    public function testGenreSetter(): void
    {
        $equipment = new Equipment();
        $genre = Genre::Jazz;
        $equipment->setGenre($genre);

        $this->assertSame($genre, $equipment->getGenre());
    }

    public function testGainRatingSetter(): void
    {
        $equipment = new Equipment();
        $gainRating = 4;
        $equipment->setGainRating($gainRating);

        $this->assertSame($gainRating, $equipment->getGainRating());
    }

    public function testBrightnessRatingSetter(): void
    {
        $equipment = new Equipment();
        $brightnessRating = 3;
        $equipment->setBrightnessRating($brightnessRating);

        $this->assertSame($brightnessRating, $equipment->getBrightnessRating());
    }

    public function testPickupTypeSetter(): void
    {
        $equipment = new Equipment();
        $pickupType = PickupType::SingleCoil;
        $equipment->setPreferredPickupType($pickupType);

        $this->assertSame($pickupType, $equipment->getPreferredPickupType());
    }
}