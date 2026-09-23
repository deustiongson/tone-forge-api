<?php

namespace App\Controller;

use App\Dto\CreateToneProfileRequest;
use App\Entity\ToneProfile;
use App\Repository\ToneProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api/v1/tone-profiles')]
class ToneProfileController extends AbstractController
{

    #[Route('', name: 'tone_profile_index', methods: ['GET'])]
    public function index(ToneProfileRepository $toneProfileRepository): JsonResponse
    {
        return $this->json($toneProfileRepository->findAll());
    }

    #[Route('/{id}', name: 'tone_profile_show', methods: ['GET'])]
    public function show(int $id, ToneProfileRepository $toneProfileRepository): JsonResponse
    {
        $toneProfile = $toneProfileRepository->find($id);

        if($toneProfile == null) {
            throw $this->createNotFoundException();
        }

        return $this->json($toneProfile);
    }

    #[Route('', name: 'tone_profile_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateToneProfileRequest $dto,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $toneProfile = new ToneProfile();
        $toneProfile->setName($dto->name)
            ->setGenre($dto->genre)
            ->setGain($dto->gain)
            ->setBrightness($dto->brightness);

        $entityManager->persist($toneProfile);
        $entityManager->flush();

        return $this->json($toneProfile, Response::HTTP_CREATED);
    }
}