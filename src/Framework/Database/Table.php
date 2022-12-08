<?php

namespace Framework\Database;

use Pagerfanta\Pagerfanta;
use PDO;

class Table
{
    /**
     * Nom de la table en BDD
     * @var string
     */
    protected string $table;

    protected ?string $entity = null;

    public function __construct(
        protected PDO $pdo
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
     * Récupère tous les enregistrements
     * @return array
     */
    public function findAll(): array
    {
        $query = $this->pdo->query("SELECT * FROM {$this->table}");

        if ($this->entity) {
            $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        } else {
            $query->setFetchMode(PDO::FETCH_OBJ);
        }

        return $query->fetchAll();
    }

    /**
     * Récupère une ligne par rapport à une valeur
     * @param string $field
     * @param string $value
     *
     * @return mixed
     * @throws NoRecordException
     */
    public function findBy(string $field, string $value): mixed
    {
        return $this->fetchOrFail("SELECT * FROM {$this->table} WHERE $field = :value", ['value' => $value]);
    }

    /**
     * Récupère un élément à partir de son id
     * @param int $id
     * @return mixed
     * @throws NoRecordException
     */
    public function find(int $id): mixed
    {
        return $this->fetchOrFail("SELECT * FROM {$this->table} WHERE id = :id", ['id' => $id]);
    }

    /**
     * Récupère le nombre d'enregistrements
     * @return int
     */
    public function count(): int
    {
        return $this->fetchColumn("SELECT COUNT(id) FROM {$this->table}");
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
        $query = $this->pdo->prepare("UPDATE {$this->table} SET {$fieldQuery} WHERE id = :id");
        return $query->execute([...$params, 'id' => $id]);
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

        $query = $this->pdo->prepare("INSERT INTO {$this->table} ({$fields}) VALUES ({$values})");
        return $query->execute($params);
    }

    /**
     * Supprime un enregistrement
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $query = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $query->execute(['id' => $id]);
    }

    private function buildFieldQuery(array $params): string
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
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        $query = $this->pdo->prepare("SELECT id FROM {$this->table} WHERE id = :id");
        $query->execute(['id' => $id]);
        return $query->fetchColumn() !== false;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Permet d'exécuter une requête et de récupérer le premier résultat
     * @param string $query
     * @param array $params
     *
     * @return mixed
     * @throws NoRecordException
     */
    protected function fetchOrFail(string $query, array $params = []): mixed
    {
        $query = $this->pdo->prepare($query);
        $query->execute($params);
        if ($this->entity) {
            $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }

        $record = $query->fetch();

        if ($record === false) {
            throw new NoRecordException();
        }

        return $record;
    }

    /**
     * Récupère la première colonne
     * @param string $query
     * @param array $params
     * @return mixed
     */
    private function fetchColumn(string $query, array $params = []): mixed
    {
        $query = $this->pdo->prepare($query);
        $query->execute($params);
        if ($this->entity) {
            $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }

        return $query->fetchColumn();
    }
}
