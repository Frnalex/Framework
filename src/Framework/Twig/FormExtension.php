<?php

namespace Framework\Twig;

use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('field', [$this, 'field'], [
                'is_safe' => ['html'],
                'needs_context' => true
            ]),
        ];
    }

    /**
     * Génère le code HTML d'un champ
     * @param array $context Contexte de la vue twig
     * @param string $key
     * @param mixed $value
     * @param string $label
     * @param array $options
     * @return string
     */
    public function field(array $context, string $key, mixed $value, string $label = '', array $options = []): string
    {
        $type = $options['type'] ?? "text";
        $error = $this->getErrorHtml($context, $key);
        $class = 'mb-3';
        $value = $this->convertValue($value);
        $attributes = [
            'class' => trim('form-control ' . ($options['class'] ?? '')),
            "name" => $key,
            "id" => $key,
        ];

        if ($error) {
            $class .= " has-danger";
            $attributes['class'] .= ' is-invalid';
        }


        if ($type === 'textarea') {
            $input = $this->textarea($value, $attributes);
        } else {
            $input = $this->input($value, $attributes);
        }

        return "
            <div class=\"{$class}\">
                <label class=\"form-label\" for=\"{$key}\">{$label}</label>
                {$input}
                {$error}
            </div>
        ";
    }

    private function convertValue(mixed $value): string
    {
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return (string)$value;
    }

    /**
     * Génère l'HTML en fonction des erreurs du contexte
     * @param array $context
     * @param string $key
     * @return string
     */
    private function getErrorHtml(array $context, string $key): string
    {
        $error = $context['errors'][$key] ?? false;

        if (!$error) {
            return "";
        }

        return "<div class=\"invalid-feedback\">{$error}</div>";
    }

    /**
     * Génère un <input>
     * @param string|null $value
     * @param array $attributes
     *
     * @return string
     */
    private function input(?string $value, array $attributes = []): string
    {
        return "<input type=\"text\" " . $this->getHtmlFromArray($attributes) . " value=\"{$value}\">";
    }

    /**
     * Génère un <textarea>
     * @param string|null $value
     * @param array $attributes
     *
     * @return string
     */
    private function textarea(?string $value, array $attributes = []): string
    {
        return "<textarea " . $this->getHtmlFromArray($attributes) . ">{$value}</textarea>";
    }

    /**
     * Transforme un tableau $clef => $valeur en attributs HTML
     * @param array $attributes
     * @return string
     */
    private function getHtmlFromArray(array $attributes): string
    {
        return implode(
            ' ',
            array_map(
                fn ($key, $value) => "{$key}=\"{$value}\"",
                array_keys($attributes),
                $attributes
            )
        );
    }
}
