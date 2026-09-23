<?php

namespace App\Controller;

use App\Repository\GuitarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Dto\CreateGuitarRequest;
use App\Entity\Guitar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/api/v1/guitars')]
class GuitarController extends AbstractController
{

    #[Route('', name: 'guitar_index', methods: ['GET'])]
    public function index(GuitarRepository $guitarRepository): JsonResponse
    {
        return $this->json($guitarRepository->findAll());
    }

    #[Route('/{id}', name: 'guitar_show', methods: ['GET'])]
    public function show(int $id, GuitarRepository $guitarRepository): JsonResponse
    {
        $guitar = $guitarRepository->find($id);

        if($guitar == null) {
            throw $this->createNotFoundException();
        }

        return $this->json($guitar);
    }

    #[Route('', name: 'guitar_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateGuitarRequest $dto,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $guitar = new Guitar();
        $guitar->setName($dto->name)
            ->setPickupType($dto->pickupType)
            ->setTuning($dto->tuning);

        $entityManager->persist($guitar);
        $entityManager->flush();

        return $this->json($guitar, Response::HTTP_CREATED);
    }
}