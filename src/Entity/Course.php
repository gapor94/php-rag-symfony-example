<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\Table(name: 'courses', schema: 'mooc')]
class Course
{
	#[ORM\Id]
	#[ORM\Column(type: Types::STRING, length: 4, options: ['fixed' => true])]
	private ?string $id = null;

	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $summary = null;

	#[ORM\Column(type: Types::JSON, options: ["jsonb" => true])]
	private array $categories = [];

	#[ORM\Column(type: Types::DATE_MUTABLE)]
	private ?\DateTimeInterface $published_at = null;

	#[ORM\Column(type: 'vector', precision: 768, nullable: true)]
	private ?array $embedding = null;

	public function getId(): ?string
	{
		return $this->id;
	}

	public function setId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	public function getSummary(): ?string
	{
		return $this->summary;
	}

	public function setSummary(string $summary): self
	{
		$this->summary = $summary;
		return $this;
	}

	public function getCategories(): array
	{
		return $this->categories;
	}

	public function setCategories(array $categories): self
	{
		$this->categories = $categories;
		return $this;
	}

	public function getPublishedAt(): ?\DateTimeInterface
	{
		return $this->published_at;
	}

	public function setPublishedAt(\DateTimeInterface $published_at): self
	{
		$this->published_at = $published_at;
		return $this;
	}

	public function getEmbedding(): ?array
	{
		return $this->embedding;
	}

	public function setEmbedding(?array $embedding): self
	{
		$this->embedding = $embedding;
		return $this;
	}
}
