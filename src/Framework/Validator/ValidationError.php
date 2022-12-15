<?php

namespace Framework\Validator;

class ValidationError
{
    private array $messages = [
        'required' => 'Le champ %s est requis',
        'empty' => 'Le champ %s ne peut être vide',
        'slug' => "Le champ %s n'est pas un slug valide",
        'minLength' => "Le champ %s doit contenir plus de %d caractères",
        'maxLength' => "Le champ %s doit contenir moins de %d caractères",
        'betweenLength' => "Le champ %s doit contenir entre %d et %d caractères",
        'datetime' => "Le champ %s doit être une date valide (%s)",
        'exists' => "Le champ %s n'existe pas dans la table %s",
        'unique' => "Le champ %s existe déjà",
        'filetype' => "Le champ %s n'est pas au format valide (%s)",
        'uploaded' => "Vous devez uploader un fichier"
    ];

    public function __construct(
        private string $key,
        private string $rule,
        private array $attributes = []
    ) {
    }

    public function __toString()
    {
        $params = [...[$this->messages[$this->rule], $this->key], ...$this->attributes];
        return call_user_func_array('sprintf', $params);
    }
}
