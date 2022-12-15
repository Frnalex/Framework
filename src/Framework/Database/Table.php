<?php

namespace Framework\Database;

use Pagerfanta\Pagerfanta;
use PDO;
use stdClass;
use Traversable;

class Table
{
    /**
     * Nom de la table en BDD
     * @var string
     */
    protected string $table;

    protected string $entity = stdClass::class;

    public function __construct(
        protected PDO $pdo
    ) {
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

    public function makeQuery(): Query
    {
        return (new Query($this->pdo))
            ->from($this->table, $this->table[0])
            ->into($this->entity);
    }

    /**
     * Récupère tous les enregistrements
     * @return Query
     */
    public function findAll(): Query
    {
        return $this->makeQuery();
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
        return $this->makeQuery()->where("$field = :field")->params(["field" => $value])->fetchOrFail();
    }

    /**
     * Récupère un élément à partir de son id
     * @param int $id
     * @return mixed
     * @throws NoRecordException
     */
    public function find(int $id): mixed
    {
        return $this->makeQuery()->where("id = $id")->fetchOrFail();
    }

    /**
     * Récupère le nombre d'enregistrements
     * @return int
     */
    public function count(): int
    {
        return $this->makeQuery()->count();
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
}
