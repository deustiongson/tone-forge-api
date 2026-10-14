<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

class EquipmentControllerTest extends ApiTestCase
{
    public function testIndexReturnsAllEquipment(): void
    {
        $this->client->request('GET', '/api/v1/equipment');

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(28, $response);
    }

    public function testShowReturnsSingleEquipmentItem(): void
    {
        $this->client->request('GET', '/api/v1/equipment/1');

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, $response['id']);
        $this->assertArrayHasKey('name', $response);
    }

    public function testShowReturns404ForNonExistingEquipment(): void
    {
        $this->client->request('GET', '/api/v1/equipment/9999');

        $this->assertResponseStatusCodeSame(404);
    }
}