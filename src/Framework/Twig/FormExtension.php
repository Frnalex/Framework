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
            'class' => trim(($options['class'] ?? '')),
            "name" => $key,
            "id" => $key,
        ];

        if ($error) {
            $class .= " has-danger";
            $attributes['class'] = trim($attributes['class'] . ' is-invalid');
        }


        if ($type === 'textarea') {
            $input = $this->textarea($value, $attributes);
        } elseif ($type === 'file') {
            $input = $this->file($attributes);
        } elseif (array_key_exists('options', $options)) {
            $input = $this->select($value, $options['options'], $attributes);
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

    private function file(array $attributes): string
    {
        return "<input type=\"file\" " . $this->getHtmlFromArray($attributes) . ">";
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
     * Génère un <select>
     * @param string|null $value
     * @param array $options
     * @param array $attributes
     *
     * @return string
     */
    private function select(?string $value, array $options, array $attributes): string
    {
        $htmlOptions = array_reduce(
            array_keys($options),
            function (string $html, string $key) use ($options, $value) {
                $params = ['value' => $key, 'selected' => $key === $value];
                return $html . "<option {$this->getHtmlFromArray($params)}>{$options[$key]}</option>";
            },
            ''
        );
        return "<select " . $this->getHtmlFromArray($attributes) . ">{$htmlOptions}</select>";
    }

    /**
     * Transforme un tableau $clef => $valeur en attributs HTML
     * @param array $attributes
     * @return string
     */
    private function getHtmlFromArray(array $attributes): string
    {
        $htmlParts = [];
        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $htmlParts[] = (string)$key;
            } elseif ($value !== false && $value !== "") {
                $htmlParts[] = "{$key}=\"{$value}\"";
            }
        }
        return implode(' ', $htmlParts);
    }
}
