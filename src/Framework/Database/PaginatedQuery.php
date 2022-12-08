<?php

namespace Framework\Database;

use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class PaginatedQuery implements AdapterInterface
{
    /**
     * @param PDO $pdo
     * @param string $query Requête permettant de récupérer X résultats
     * @param string $countQuery Requête permettant de compter le nombre de résultats
     * @param string|null $entity
     * @param array $params
     */
    public function __construct(
        private PDO $pdo,
        private string $query,
        private string $countQuery,
        private ?string $entity,
        private array $params = []
    ) {
    }

    /**
     * Returns the number of results for the list.
     */
    public function getNbResults(): int
    {
        if (!empty($this->params)) {
            $query = $this->pdo->prepare($this->countQuery);
            $query->execute($this->params);
            return $query->fetchColumn();
        }
        return $this->pdo->query($this->countQuery)->fetchColumn();
    }

    /**
     * Returns an slice of the results representing the current page of items in the list.
     *
     * @param int $offset
     * @param int $length
     *
     * @return array
     */
    public function getSlice(int $offset, int $length): array
    {
        $statement = $this->pdo->prepare($this->query . ' LIMIT :offset, :length');
        foreach ($this->params as $key => $param) {
            $statement->bindParam($key, $param);
        }
        $statement->bindParam('offset', $offset, PDO::PARAM_INT);
        $statement->bindParam('length', $length, PDO::PARAM_INT);
        if ($this->entity) {
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }
        $statement->execute();
        return $statement->fetchAll();
    }
}
