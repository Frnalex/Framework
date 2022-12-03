<?php

namespace Framework\Database;

use Pagerfanta\Pagerfanta;
use PDO;
use PHPUnit\Framework\Constraint\ArrayHasKey;

class Table
{
    /**
     * Nom de la table en BDD
     * @var string
     */
    protected string $table;

    protected ?string $entity = null;

    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Pagine les éléments
     * @param int $perPage
     *
     * @return Pagerfanta
     */
    public function findPaginated(int $perPage, int $currentpage): Pagerfanta
    {
        $query = new PaginatedQuery(
            $this->pdo,
            $this->paginationQuery(),
            "SELECT count(id) FROM {$this->table}",
            $this->entity
        );

        $pagerFanta = (new Pagerfanta($query))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentpage)
        ;

        return $pagerFanta;
    }

    protected function paginationQuery(): string
    {
        return "SELECT * FROM {$this->table}";
    }

    /**
     * Récupère une liste clef => valeur de nos enregistrements
     * @return array
     */
    public function findList(): array
    {
        $results = $this->pdo->query("SELECT id, name FROM {$this->table}")->fetchAll(PDO::FETCH_NUM);

        $list = [];
        foreach ($results as $result) {
            [$id, $name] = $result;
            $list[$id] = $name;
        }

        return $list;
    }

    /**
     * Récupère un élément à partir de son id
     * @param int $id
     * @return mixed
     */
    public function find(int $id): mixed
    {
        $query = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $query->execute([$id]);
        if ($this->entity) {
            $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }
        return $query->fetch() ?: null;
    }

    /**
     * Met à jour un enregistrement au niveau de la base de donnée
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function update(int $id, array $params): bool
    {
        $fieldQuery = $this->buildFieldQuery($params);
        $statement = $this->pdo->prepare("UPDATE {$this->table} SET {$fieldQuery} WHERE id = :id");
        return $statement->execute([...$params, 'id' => $id]);
    }

    /**
     * Créé un nouvel enregistrement
     * @param array $params
     * @return bool
     */
    public function insert(array $params): bool
    {
        $fields = join(', ', array_keys($params));
        $values = join(', ', array_map(fn ($field) => ":{$field}", array_keys($params)));

        $statement = $this->pdo->prepare("INSERT INTO {$this->table} ({$fields}) VALUES ({$values})");
        return $statement->execute($params);
    }

    /**
     * Supprime un enregistrement
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $statement->execute(['id' => $id]);
    }

    private function buildFieldQuery(array $params)
    {
        return join(', ', array_map(fn ($field) => "{$field} = :{$field}", array_keys($params)));
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Vérifie qu'un enregistrement existe
     * @param string $id
     * @return bool
     */
    public function exists(string $id): bool
    {
        $statement = $this->pdo->prepare("SELECT id FROM {$this->table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        return $statement->fetchColumn() !== false;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
