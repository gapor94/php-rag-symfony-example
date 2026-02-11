<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CourseRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/courses')]
class CourseController extends AbstractController
{
    public function __construct(private readonly CourseRepository $courseRepository)
    {
    }

    /**
     * @throws \JsonException
     * @throws Exception
     */
    #[Route('/similar', methods: ['GET'])]
    public function searchSimilar(Request $request): JsonResponse
    {
        $idsParam = $request->query->getString('ids');

        if (empty($idsParam)) {
            return $this->json(['error' => 'ids parameter is required'], 400);
        }

        $ids = explode(',', $idsParam);

        $results = $this->courseRepository->searchSimilar($ids);

        return $this->json($results);
    }
}
