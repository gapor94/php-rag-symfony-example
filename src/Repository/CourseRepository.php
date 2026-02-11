<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;


/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly OllamaEmbeddingsProvider $ollama)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function searchSimilar(array $ids): array
    {
        $coursesToSearchSimilar = $this->findBy(['id' => $ids]);

        if (empty($coursesToSearchSimilar)) {
            return [];
        }

        $documents = array_map(
            static fn(Course $c) => new Document(json_encode([
                'id'          => $c->getId(),
                'name'        => $c->getName(),
                'summary'     => $c->getSummary(),
                'categories'  => $c->getCategories(),
                'publishedAt' => $c->getPublishedAt()?->format('Y-m-d'),
            ], JSON_THROW_ON_ERROR)),
            $coursesToSearchSimilar
        );

        $embeddedDocs = $this->ollama->embedDocuments($documents);

        // centroide
        $dimension = count($embeddedDocs[0]->embedding);
        $averaged = array_fill(0, $dimension, 0.0);

        foreach ($embeddedDocs as $doc) {
            foreach ($doc->embedding as $i => $val) {
                $averaged[$i] += $val;
            }
        }

        $count = count($embeddedDocs);
        $averaged = array_map(static fn(float $v) => $v / $count, $averaged);

        $embeddingVector = '[' . implode(',', $averaged) . ']';

        return $this->findSimilarExcluding($ids, $embeddingVector, 0.001);
    }

    /**
     * @throws Exception
     */
    public function findSimilarExcluding(array $excludeIds, string $embeddingVector, float $recencyWeight): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $plainIds = '{' . implode(',', $excludeIds) . '}';

        $sql = sprintf(
            'SELECT id, name, summary, categories, published_at
             FROM mooc.courses
             WHERE id != ALL(:excludeIds::text[])
             ORDER BY (embedding <=> %s) + %s * EXTRACT(EPOCH FROM NOW() - published_at) / 86400
             LIMIT 10',
            $conn->quote($embeddingVector),
            $recencyWeight
        );

        return $conn->executeQuery($sql, ['excludeIds' => $plainIds])->fetchAllAssociative();
    }
}
