<?php

namespace Framework;

use Framework\Validator\ValidationError;
use PDO;
use Psr\Http\Message\UploadedFileInterface;

class Validator
{
    private const MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
    ];

    /**
     * @var ValidationError[]
     */
    private array $errors = [];

    public function __construct(
        private array $params
    ) {
    }

    /**
     * Vérifie que les champs sont présents dans le tableau
     * @param string ...$keys
     * @return Validator
     */
    public function required(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value)) {
                $this->addError($key, "required");
            }
        }

        return $this;
    }

    /**
     * Vérifie que le champ n'est pas vide
     * @param string ...$keys
     * @return Validator
     */
    public function notEmpty(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value) || empty($value)) {
                $this->addError($key, "empty");
            }
        }

        return $this;
    }

    public function length(string $key, ?int $min, ?int $max = null): self
    {
        $value = $this->getValue($key);
        $length = mb_strlen($value);
        if (!is_null($min) && !is_null($max) && ($length < $min || $length > $max)) {
            $this->addError($key, 'betweenLength', [$min, $max]);
            return $this;
        }
        if (!is_null($min) && $length < $min) {
            $this->addError($key, 'minLength', [$min]);
            return $this;
        }
        if (!is_null($max) && $length > $max) {
            $this->addError($key, 'maxLength', [$max]);
            return $this;
        }
        return $this;
    }

    /**
     * Vérifie que l'élément est un slug
     * @param string $key
     * @return Validator
     */
    public function slug(string $key): self
    {
        $value = $this->getValue($key);
        $pattern = '/^[a-z0-9]+(-[a-z0-9]+)*$/';
        if (!is_null($value) && !preg_match($pattern, $value)) {
            $this->addError($key, 'slug');
        }
        return $this;
    }

    /**
     * Vérifie que la clef est une date valide
     * @param string $key
     * @param string $format
     * @return self
     */
    public function dateTime(string $key, string $format = 'Y-m-d H:i:s'): self
    {
        $value = $this->getValue($key);
        $date = \DateTime::createFromFormat($format, $value);
        $errors = \DateTime::getLastErrors();

        if ($errors['error_count'] > 0 || $errors['warning_count'] > 0 || $date === false) {
            $this->addError($key, 'datetime', [$format]);
        }

        return $this;
    }

    /**
     * Vérifie que la clef existe dans table
     * @param string $key
     * @param string $table
     * @param PDO $pdo
     *
     * @return self
     */
    public function exists(string $key, string $table, PDO $pdo): self
    {
        $value = $this->getValue($key);
        $statement = $pdo->prepare("SELECT id FROM {$table} WHERE id = :id");
        $statement->execute(['id' => $value]);
        if ($statement->fetchColumn() === false) {
            $this->addError($key, 'exists', [$table]);
        }

        return $this;
    }

    /**
     * Vérifie que la clef est unique
     * @param string $key
     * @param string $table
     * @param PDO $pdo
     * @param int|null $exclude
     *
     * @return self
     */
    public function unique(string $key, string $table, PDO $pdo, int $exclude = null): self
    {
        $value = $this->getValue($key);
        $query = "SELECT id FROM {$table} WHERE $key = :value";
        $params = ['value' => $value];
        if ($exclude !== null) {
            $query .= " AND id != :id";
            $params['id'] = $exclude;
        }
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        if ($statement->fetchColumn() !== false) {
            $this->addError($key, 'unique', [$value]);
        }

        return $this;
    }

    /**
     * Vérifie si le fichier a bien été uploadé
     * @param string $key
     *
     * @return self
     */
    public function uploaded(string $key): self
    {
        /** @var UploadedFileInterface $file */
        $file = $this->getValue($key);

        if ((!$file instanceof UploadedFileInterface) || ($file->getError() !== UPLOAD_ERR_OK)) {
            $this->addError($key, 'uploaded');
        }

        return $this;
    }

    /**
     * Vérifie le format de fichier
     * @param string $key
     * @param array $extensions
     * @return self
     */
    public function extension(string $key, array $extensions): self
    {
        /** @var UploadedFileInterface $file */
        $file = $this->getValue($key);

        if ($file !== null &&  $file->getError() === UPLOAD_ERR_OK) {
            $type = $file->getClientMediaType();
            $extension = mb_strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $expectedType = self::MIME_TYPES[$extension] ?? null;
            if (!in_array($extension, $extensions) || $expectedType !== $type) {
                $this->addError($key, 'filetype', [join(', ', $extensions)]);
            }
        }

        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Récupère les erreurs
     * @return ValidationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Ajoute une erreur
     * @param string $key
     * @param string $rule
     * @param array $attributes
     */
    private function addError(string $key, string $rule, array $attributes = []): void
    {
        $this->errors[$key] = new ValidationError($key, $rule, $attributes);
    }

    private function getValue(string $key): mixed
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return null;
    }
}
