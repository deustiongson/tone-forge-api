<?php

namespace App\Tests\Entity;

use App\Entity\Guitar;
use App\Enum\PickupType;
use PHPUnit\Framework\TestCase;

class GuitarTest extends TestCase
{
    public function testGettersAndSettersRoundTrip(): void
    {
        $guitar = new Guitar();

        $guitar->setName('Test Strat')
            ->setPickupType(PickupType::SingleCoil)
            ->setTuning('E Standard');

        $this->assertSame('Test Strat', $guitar->getName());
        $this->assertSame(PickupType::SingleCoil, $guitar->getPickupType());
        $this->assertSame('E Standard', $guitar->getTuning());
        
    }
}