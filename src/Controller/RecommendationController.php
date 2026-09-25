<?php

namespace App\Controller;

use App\Dto\CreateRecommendationRequest;
use App\Service\RecommendationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/recommendations')]
class RecommendationController extends AbstractController {

    #[Route('', name: 'recommendation_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateRecommendationRequest $dto,
        RecommendationService $recommendationService
    ): JsonResponse {

        $result = $recommendationService->recommend($dto->guitarId, $dto->toneProfileId);

        return $this->json($result);
    }
}