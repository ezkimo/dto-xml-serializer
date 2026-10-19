<?php

declare(strict_types=1);

namespace MMNewmedia\Serializer;

use BackedEnum;
use MMNewmedia\Xsd\Enumeration;
use ReflectionClass;
use RuntimeException;
use Stringable;

/**
 * Ermittelt Xsd\Enumeration Deklarationen und prüft Werte gegen die deklarierte Wertmenge.
 *
 * Die Traits werden von den Serializern verwendet, um simple Content Werte vor dem
 * Serialisieren bzw. nach dem Deserialisieren gegen die Enumeration zu validieren.
 */
trait EnumerationValidator
{
    /**
     * Liefert das erste Xsd\Enumeration Attribut im Vererbungsbaum der übergebenen Klasse.
     *
     * @param class-string $className
     */
    protected function getEnumeration(string $className): ?Enumeration
    {
        $reflector = new ReflectionClass($className);

        do {
            $attribute = $reflector->getAttributes(Enumeration::class)[0] ?? null;

            if ($attribute !== null) {
                return $attribute->newInstance();
            }
        } while ($reflector = $reflector->getParentClass());

        return null;
    }

    /**
     * Wirft eine RuntimeException, sofern die übergebene Klasse eine validierte Enumeration
     * deklariert und der Wert nicht zur deklarierten Wertmenge gehört. Werte, die keine
     * Skalare oder BackedEnum Instanzen sind (z.B. DateTimeInterface), werden übersprungen.
     *
     * @param class-string $className
     */
    protected function assertEnumerationValue(string $className, mixed $value): void
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (is_int($value) === false && is_float($value) === false && is_string($value) === false) {
            return;
        }

        $enumeration = $this->getEnumeration($className);

        if ($enumeration === null || $enumeration->validate === false) {
            return;
        }

        if (in_array(strval($value), $enumeration->values, true) === false) {
            throw new RuntimeException(sprintf(
                'Value %s is not part of enumeration %s (%s).',
                $this->quoteEnumerationValue($value),
                $this->quoteEnumerationValue($enumeration->name ?? $enumeration->enum),
                implode(', ', $enumeration->values)
            ));
        }
    }

    /**
     * Setzt den übergebenen Wert in einfache Anführungszeichen, wie sie in XSD
     * Enumeration Facets verwendet werden.
     */
    private function quoteEnumerationValue(string|Stringable|int|float $value): string
    {
        return "'" . strval($value) . "'";
    }
}
