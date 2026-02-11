<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
    name: 'app:import-courses',
    description: 'Import courses from JSON into the database',
)]
class ImportCoursesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $projectDir,
        private readonly OllamaEmbeddingsProvider $ollama
    )
    {
        parent::__construct();
    }

    /**
     * @throws \DateMalformedStringException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $jsonPath = $this->projectDir . '/resources/courses.json';
        $courses = json_decode(file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);

        $io->info(sprintf('Found %d courses to import', count($courses)));

        $io->progressStart(count($courses));

        foreach ($courses as $data) {
            $content = json_encode([
                'id'          => $data['id'],
                'name'        => $data['name'],
                'summary'     => $data['summary'],
                'categories'  => $data['categories'],
                'publishedAt' => $data['published_at'],
            ], JSON_THROW_ON_ERROR);

            $document = new Document($content);
            $embeddedDoc = $this->ollama->embedDocument($document);

            $course = new Course();
            $course->setId($data['id']);
            $course->setName($data['name']);
            $course->setSummary($data['summary']);
            $course->setCategories($data['categories']);
            $course->setPublishedAt(new \DateTime($data['published_at']));
            $course->setEmbedding($embeddedDoc->embedding);

            $this->em->persist($course);

            $io->progressAdvance();
        }

        $this->em->flush();
        $io->progressFinish();

        $io->success(sprintf('Imported %d courses with embeddings', count($courses)));

        return Command::SUCCESS;
    }
}
