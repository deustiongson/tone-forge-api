<?php

namespace App\Tests\Entity;

use App\Enum\Genre;
use App\Entity\ToneProfile;
use PHPUnit\Framework\TestCase;

class ToneProfileTest extends TestCase
{
    public function testNameSetter(): void
    {
       $toneProfile = new ToneProfile();
       $name = 'Test Profile';
       $toneProfile->setName($name);

       $this->assertSame($name, $toneProfile->getName());
    }

    public function testGenreSetter(): void
    {
        $toneProfile = new ToneProfile();
        $genre = Genre::Rock;
        $toneProfile->setGenre($genre);

        $this->assertSame($genre, $toneProfile->getGenre());
    }

    public function testGainSetter(): void
    {
        $toneProfile = new ToneProfile();
        $gain = 7;
        $toneProfile->setGain($gain);

        $this->assertSame($gain, $toneProfile->getGain());
    }

    public function testBrightnessSetter(): void
    {
        $toneProfile = new ToneProfile();
        $brightness = 5;
        $toneProfile->setBrightness($brightness);

        $this->assertSame($brightness, $toneProfile->getBrightness());
    }
}