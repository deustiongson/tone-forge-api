<?php

namespace App\Controller;

use App\Repository\EquipmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/equipment')]
class EquipmentController extends AbstractController
{

    #[Route('', name: 'equipment_index', methods: ['GET'])]
    public function index(EquipmentRepository $equipmentRepository): JsonResponse
    {
        return $this->json($equipmentRepository->findAll());
    }

    #[Route('/{id}', name: 'equipment_show', methods: ['GET'])]
    public function show(int $id, EquipmentRepository $equipmentRepository): JsonResponse
    {
        $equipment = $equipmentRepository->find($id);

        if($equipment == null) {
            throw $this->createNotFoundException();
        }

        return $this->json($equipment);
    }
}