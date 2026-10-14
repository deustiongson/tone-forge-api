<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

class ToneProfileControllerTest extends ApiTestCase
{
    public function testCreateToneProfilePersistsAndReturnsIt(): void
    {
        $name = 'Test Tone Profile';
        $genre = 'rock';
        $gain = 7;
        $brightness = 8;

        $this->client->request(
            'POST',
            '/api/v1/tone-profiles',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => $name,
                'genre' => $genre,
                'gain' => $gain,
                'brightness' => $brightness
            ]),
        );

        $this->assertResponseStatusCodeSame(201);

        $created = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($name, $created['name']);
        $this->assertSame($genre, $created['genre']);
        $this->assertSame($gain, $created['gain']);
        $this->assertSame($brightness, $created['brightness']);

        //Fetch to check if it was persisted to the database
        $this->client->request('GET', '/api/v1/tone-profiles/'.$created['id']);

        $this->assertResponseIsSuccessful();

        $fetched = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($created, $fetched);
    }

    public function testCreateToneProfileWithGainAboveTenReturns422(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/tone-profiles',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Progressive Metal',
                'genre' => 'Metal',
                'gain' => 12,
                'brightness' => 8
            ]),
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testShowReturns404ForNonexistentToneProfile(): void
    {
        $this->client->request('GET', '/api/v1/tone-profiles/9999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testIndexReturnsRecentlyCreatedToneProfiles(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/tone-profiles',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Djent',
                'genre' => 'metal',
                'gain' => 9,
                'brightness' => 8
            ]),
        );

        $this->client->request('GET', '/api/v1/tone-profiles');

        $this->assertResponseIsSuccessful();

        $toneProfiles = json_decode($this->client->getResponse()->getContent(), true);

        //ApiTestCase has rollback, table is empty at start of every test
        $this->assertCount(1, $toneProfiles);
    }
}