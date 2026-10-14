<?php

namespace App\Tests\Entity;

use App\Entity\Guitar;
use App\Enum\PickupType;
use PHPUnit\Framework\TestCase;

class GuitarTest extends TestCase
{
    public function testNameSetter(): void
    {
        $guitar = new Guitar();
        $name = 'Test Strat';
        $guitar->setName($name);
            
        $this->assertSame($name, $guitar->getName());
    }

    public function testPickupTypeSetter(): void
    {
        $guitar = new Guitar();
        $pickupType = PickupType::SingleCoil;
        $guitar->setPickupType($pickupType);
        
        $this->assertSame($pickupType, $guitar->getPickupType());
    }

    public function testTuningSetter(): void
    {
        $guitar = new Guitar();
        $tuning = 'E Standard';
        $guitar->setTuning($tuning);
        
        $this->assertSame($tuning, $guitar->getTuning());
    }
}