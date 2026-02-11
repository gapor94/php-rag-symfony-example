# Symfony RAG Project

This project is a reference implementation of a **RAG (Retrieval-Augmented Generation)** system using Symfony and local
AI models. It enables data ingestion, embedding generation, and advanced semantic similarity search.

## Data Source & Credits

The course data used in this project is provided by **[Codely](https://codely.com)**, as part of their course 
**["IA: Embeddings y RAG"](https://pro.codely.com/library/ia-embeddings-y-rag-230838/655241/about/)**.

> [!IMPORTANT]
> This repository is a custom implementation that adapts the concepts from the aforementioned course to a modern stack
> using Docker, FrankenPHP, and cutting-edge libraries.

---

## Technical Stack

The project is built upon the following core technologies:

* **[Symfony Docker](https://github.com/dunglas/symfony-docker)**: High-performance base skeleton by **Kévin Dunglas**
  featuring **FrankenPHP** (without mercure and vulcain).
* **[partitech/doctrine-pgvector](https://github.com/Partitech/doctrine-pgvector)**: A
  Doctrine extension that adds support for the PostgreSQL `vector` data type.
* **[neuron-core/neuron-ai](https://github.com/neuron-core/neuron-ai)**: A PHP framework
  for managing AI workflows, documents, and embedding providers.
* **Ollama**: A local engine for running LLMs and embedding models (configured with `nomic-embed-text`).

---

## Getting Started

1. **Install Docker Compose** (v2.10+) if you haven't
   already: [installation guide](https://docs.docker.com/compose/install/).
2. Run `docker compose build --pull --no-cache` to build fresh images.
3. Run `docker compose up --wait` to set up and start the Symfony project.
4. Open `https://localhost` in your browser
   and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334).
5. To stop the containers: `docker compose down --remove-orphans`.

---

## Ollama Setup

This project requires Ollama for local AI processing.

### 1. Installation

Download it at [ollama.com](https://ollama.com) or run the following command on Linux:

```bash
curl -fsSL https://ollama.com/install.sh | sh
```

### 2. Configure Host (Docker-to-Host Communication)

By default, Ollama only listens to `localhost`. Since Symfony is running inside Docker, you must allow external
connections by setting the host to `0.0.0.0`.

#### **Linux Options:**

**Option A: Permanent (Systemd Service)**

1. Edit the service configuration: `sudo systemctl edit ollama.service`
2. Add these lines:

```ini
[Service]
Environment = "OLLAMA_HOST=0.0.0.0"
```

3. Save and restart: `sudo systemctl daemon-reload && sudo systemctl restart ollama`

**Option B: Manual (Terminal)**

1. Stop the existing service: `sudo systemctl stop ollama`
2. Run it manually with the host variable: `OLLAMA_HOST=0.0.0.0 ollama serve`

#### **Windows / macOS**

1. Quit the Ollama application from the tray icon.
2. Open your terminal and run:

```bash
OLLAMA_HOST=0.0.0.0 ollama serve
```

### 3. Download the Required Model

Run the following command to pull the embedding model:

```bash
ollama pull nomic-embed-text
```

---

## How it works (Usage)

### 1. Data Ingestion

To import the courses and generate their embeddings in the vector database, run:

```bash
make sf c=app:import-courses
```

### 2. Semantic Search (Retrieval)

Once imported, you can search for similar courses via the dedicated endpoint. The system calculates the
**average vector** (centroid) of the specified courses and retrieves the nearest neighbors using **Cosine Similarity**.

**Example Request:**
`GET https://localhost/api/courses/similar?ids=cl3c,cl4d`
