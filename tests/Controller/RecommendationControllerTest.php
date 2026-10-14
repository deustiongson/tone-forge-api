<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

class RecommendationControllerTest extends ApiTestCase
{
    public function testRecommendReturnsExpectedShape(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/guitars',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Test Guitar',
                'pickupType' => 'humbucker',
                'tuning' => 'E Standard'
            ]),
        );

        $guitar = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request(
            'POST',
            '/api/v1/tone-profiles',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Blues Lead',
                'genre' => 'blues',
                'gain' => 7,
                'brightness' => 6
            ]),
        );

        $toneProfile = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request(
            'POST',
            '/api/v1/recommendations',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'guitarId' => $guitar['id'],
                'toneProfileId' => $toneProfile['id']
            ]),
        );

        $this->assertResponseIsSuccessful();

        $result = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($guitar, $result['guitar']);
        $this->assertSame($toneProfile, $result['toneProfile']);
        $this->assertNotNull($result['amplifier']);
        $this->assertNotNull($result['cabinet']);
        $this->assertIsArray($result['effects']);
    }

    public function testRecommendWithNonexistentGuitarReturns404(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/tone-profiles',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Blues Lead',
                'genre' => 'blues',
                'gain' => 7,
                'brightness' => 6
            ]),
        );

        $toneProfile = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request(
            'POST',
            '/api/v1/recommendations',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'guitarId' => 99999,
                'toneProfileId' => $toneProfile['id']
            ]),
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRecommendWithNonexistentToneProfileReturns404(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/guitars',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Test Guitar',
                'pickupType' => 'humbucker',
                'tuning' => 'E Standard'
            ]),
        );

        $guitar = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->request(
            'POST',
            '/api/v1/recommendations',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'guitarId' => $guitar['id'],
                'toneProfileId' => 99999
            ]),
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRecommendWithInvalidPayloadReturns422(): void
    {
                $this->client->request(
            'POST',
            '/api/v1/recommendations',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'guitarId' => -1,
                'toneProfileId' => -1
            ]),
        );

        $this->assertResponseStatusCodeSame(422);
    }
}