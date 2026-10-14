<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

class GuitarControllerTest extends ApiTestCase
{
    public function testCreateGuitarPersistsAndReturnsIt(): void
    {   
        $guitarName = 'Test Telecaster';
        $pickupType = 'single-coil';

        $this->client->request(
            'POST',
            '/api/v1/guitars',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => $guitarName,
                'pickupType' => $pickupType,
                'tuning' => 'E-standard',
            ]),
        );

        $this->assertResponseStatusCodeSame(201);

        $created = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($guitarName, $created['name']);
        $this->assertSame($pickupType, $created['pickupType']);

        //Fetch to check if it was persisted to the database
        $this->client->request('GET', '/api/v1/guitars/'.$created['id']);

        $this->assertResponseIsSuccessful();

        $fetched = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($created, $fetched);
    }

    public function testCreateGuitarWithBlankNameReturns422(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/guitars',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => '',
                'pickupType' => 'single-coil',
                'tuning' => 'E-standard',
            ]),
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testShowReturns404ForNonexistentGuitar(): void
    {
        $this->client->request('GET', '/api/v1/guitars/9999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testIndexReturnsRecentlyCreatedGuitars(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/guitars',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Telecaster',
                'pickupType' => 'single-coil',
                'tuning' => 'E-standard',
            ]),
        );

        $this->client->request('GET', '/api/v1/guitars');

        $this->assertResponseIsSuccessful();

        $guitars = json_decode($this->client->getResponse()->getContent(), true);

        //ApiTestCase has rollback, Guitar table is empty at start of every test
        $this->assertCount(1, $guitars);
    }
}