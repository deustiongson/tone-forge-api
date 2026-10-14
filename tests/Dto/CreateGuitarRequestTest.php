<?php

namespace App\Tests\Dto;

use App\Dto\CreateGuitarRequest;
use App\Enum\PickupType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateGuitarRequestTest extends TestCase
{
    private ValidatorInterface $validator;

    public function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidPaloadHasNoViolations(): void
    {
        $violations = $this->validator->validate($this->buildValidRequest());

        $this->assertCount(0, $violations);
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

    public function testNullPickupTypeIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->pickupType = null;

        $violations = $this->validator->validate($dto);

        $this->assertCount(1, $violations);
        $this->assertSame('pickupType', $violations[0]->getPropertyPath());
    }

    public function testBlankTuningIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->tuning = '';

        $violations = $this->validator->validate($dto);

        //the tuning property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('tuning', $violations[0]->getPropertyPath());
    }

    private function buildValidRequest(): CreateGuitarRequest
    {
        $dto = new CreateGuitarRequest();
        $dto->name = 'Gibson Les Paul';
        $dto->pickupType = PickupType::Humbucker;
        $dto->tuning = 'Eb Standard';

        return $dto;
    }
}