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
        $guitar->setName('Test Strat');
            
        $this->assertSame('Test Strat', $guitar->getName());
    }

    public function testPickupTypeSetter(): void
    {
        $guitar = new Guitar();
        $guitar->setPickupType(PickupType::SingleCoil);
        
        $this->assertSame(PickupType::SingleCoil, $guitar->getPickupType());
    }

    public function testTuningSetter(): void
    {
        $guitar = new Guitar();
        $guitar->setTuning('E Standard');
        
        $this->assertSame('E Standard', $guitar->getTuning());
    }
}