<?php

namespace App\Tests\Dto;

use App\Dto\CreateToneProfileRequest;
use App\Enum\Genre;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateToneProfileRequestTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setup(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidPayloadHasNoViolations(): void
    {
        $violations = $this->validator->validate($this->buildValidRequest());

        $this->assertCount(0, $violations);
    }

    public function testZeroGainIsValid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->gain = 0;

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testGainAboveTenIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->gain = 11;

        $violations = $this->validator->validate($dto);

        //the gain property should be invalid 
        $this->assertCount(1, $violations);
        $this->assertSame('gain', $violations[0]->getPropertyPath());
    }

    public function testGainBelowZeroIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->gain = -3;

        $violations = $this->validator->validate($dto);

        //the gain property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('gain', $violations[0]->getPropertyPath());
    }

    public function testZeroBrightnessIsValid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->brightness = 0;

        $violations = $this->validator->validate($dto);

        $this->assertCount(0, $violations);
    }

    public function testBrightnessAboveTenIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->brightness = 11;

        $violations = $this->validator->validate($dto);

        //the brightness property should be invalid 
        $this->assertCount(1, $violations);
        $this->assertSame('brightness', $violations[0]->getPropertyPath());
    }

    public function testBrightnessBelowZeroIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->brightness = -1;

        $violations = $this->validator->validate($dto);

        //the brightness property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('brightness', $violations[0]->getPropertyPath());
    }

    public function testNullGenreIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->genre = null;

        $violations = $this->validator->validate($dto);

        //the genre property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('genre', $violations[0]->getPropertyPath());
    }

    public function testNullGainIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->gain = null;

        $violations = $this->validator->validate($dto);

        //the gain property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('gain', $violations[0]->getPropertyPath());
    }

    public function testNullBrightnessIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->brightness = null;

        $violations = $this->validator->validate($dto);

        //the brightness property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('brightness', $violations[0]->getPropertyPath());
    }

    public function testBlankNameIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->name = '';

        $violations = $this->validator->validate($dto);

        //the name property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('name', $violations[0]->getPropertyPath());
    }

    private function buildValidRequest(): CreateToneProfileRequest
    {
        $dto = new CreateToneProfileRequest();
        $dto->name = "Texas Blues";
        $dto->genre = Genre::Blues;
        $dto->gain = 7;
        $dto->brightness = 6;

        return $dto;
    }
}