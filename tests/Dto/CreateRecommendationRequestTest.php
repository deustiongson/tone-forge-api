<?php

namespace App\Tests\Dto;

use App\Dto\CreateRecommendationRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateRecommendationRequestTest extends TestCase
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

    //guitarId = null
    public function testNullGuitarIdIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->guitarId = null;

        $violations = $this->validator->validate($dto);

        //the guitarId property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('guitarId', $violations[0]->getPropertyPath());
    }

    //guitarId = 0
    public function testZeroGuitarIdIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->guitarId = 0;

        $violations = $this->validator->validate($dto);

        //the guitarId property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('guitarId', $violations[0]->getPropertyPath());
    }

    //guitarId = -1
    public function testNegativeGuitarIdIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->guitarId = -1;

        $violations = $this->validator->validate($dto);

        //the guitarId property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('guitarId', $violations[0]->getPropertyPath());
    }
    
    //toneProfileId = null
    public function testNullToneProfileIdIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->toneProfileId = null;

        $violations = $this->validator->validate($dto);

        //the toneProfileId property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('toneProfileId', $violations[0]->getPropertyPath());
    }

    //toneProfileId = 0
    public function testZeroToneProfileIdIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->toneProfileId = 0;

        $violations = $this->validator->validate($dto);

        //the toneProfileId property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('toneProfileId', $violations[0]->getPropertyPath());
    }

    //toneProfileId = -1
    public function testNegativeToneProfileIdIsInvalid(): void
    {
        $dto = $this->buildValidRequest();
        $dto->toneProfileId = -1;

        $violations = $this->validator->validate($dto);

        //the toneProfileId property should be invalid
        $this->assertCount(1, $violations);
        $this->assertSame('toneProfileId', $violations[0]->getPropertyPath());
    }
    
    private function buildValidRequest(): CreateRecommendationRequest
    {
        $dto = new CreateRecommendationRequest();
        $dto->guitarId = 1;
        $dto->toneProfileId = 1;

        return $dto;
    }
}