<?php

declare(strict_types=1);

namespace MMNewmedia\Serializer;

use BackedEnum;
use MMNewmedia\Xsd\Pattern;
use ReflectionClass;
use RuntimeException;
use Stringable;

/**
 * Ermittelt Xsd\Pattern Deklarationen und prüft Werte gegen die deklarierten Patterns.
 *
 * Die Traits werden von den Serializern verwendet, um simple Content Werte vor dem
 * Serialisieren bzw. nach dem Deserialisieren gegen die Pattern zu validieren.
 */
trait PatternValidator
{
    /**
     * Liefert alle Xsd\Pattern Attribute im Vererbungsbaum der übergebenen Klasse.
     *
     * @param class-string $className
     * @return list<Pattern>
     */
    protected function getPatterns(string $className): array
    {
        $patterns = [];
        $reflector = new ReflectionClass($className);

        do {
            $attributes = $reflector->getAttributes(Pattern::class);
            foreach ($attributes as $attribute) {
                $patterns[] = $attribute->newInstance();
            }
        } while ($reflector = $reflector->getParentClass());

        return $patterns;
    }

    /**
     * Wirft eine RuntimeException, sofern die übergebene Klasse Pattern
     * deklariert und der Wert nicht mit allen deklarierten Patterns übereinstimmt.
     * Werte, die keine Skalare oder BackedEnum Instanzen sind (z.B. DateTimeInterface), werden übersprungen.
     *
     * @param class-string $className
     */
    protected function assertPatternValues(string $className, mixed $value): void
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (is_int($value) === false && is_float($value) === false && is_string($value) === false) {
            return;
        }

        $patterns = $this->getPatterns($className);

        if ($patterns === []) {
            return;
        }

        $stringValue = strval($value);

        foreach ($patterns as $pattern) {
            $patternValue = $pattern->value;

            $escaped = str_replace('#', '\\#', $patternValue);
            $result = @preg_match('#' . $escaped . '#', $stringValue);

            if ($result === false) {
                continue;
            }

            if ($result === 0) {
                throw new RuntimeException(sprintf(
                    'Value %s does not match pattern %s.',
                    $this->quotePatternValue($value),
                    $this->quotePatternValue($patternValue)
                ));
            }
        }
    }

    /**
     * Setzt den übergebenen Wert in einfache Anführungszeichen.
     */
    private function quotePatternValue(string|Stringable|int|float $value): string
    {
        return "'" . strval($value) . "'";
    }
}
