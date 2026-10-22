<?php

declare(strict_types=1);

namespace MMNewmedia\Serializer;

use BackedEnum;
use MMNewmedia\Xsd\MaxLength;
use MMNewmedia\Xsd\MinLength;
use ReflectionClass;
use RuntimeException;

/**
 * Ermittelt Xsd\MinLength/Xsd\MaxLength Deklarationen und prüft Werte gegen die deklarierten Längengrenzen.
 *
 * Die Traits werden von den Serializern verwendet, um simple Content Werte vor dem
 * Serialisieren bzw. nach dem Deserialisieren gegen die Längenbeschränkungen zu validieren.
 */
trait StringLengthValidator
{
    /**
     * Liefert die striktesten Längengrenzen im Vererbungsbaum der übergebenen Klasse.
     *
     * Mehrere Restriction-Stufen werden wie in XSD als Schnittmenge behandelt:
     * die größte minLength und die kleinste maxLength gewinnen.
     *
     * @param class-string $className
     * @return array{min: null|int, max: null|int}
     */
    protected function getLengthBounds(string $className): array
    {
        $min = null;
        $max = null;
        $reflector = new ReflectionClass($className);

        do {
            $minLength = $reflector->getAttributes(MinLength::class)[0] ?? null;
            if ($minLength !== null) {
                $value = $minLength->newInstance()->value;
                $min = $min === null ? $value : max($min, $value);
            }

            $maxLength = $reflector->getAttributes(MaxLength::class)[0] ?? null;
            if ($maxLength !== null) {
                $value = $maxLength->newInstance()->value;
                $max = $max === null ? $value : min($max, $value);
            }
        } while ($reflector = $reflector->getParentClass());

        return ['min' => $min, 'max' => $max];
    }

    /**
     * Wirft eine RuntimeException, sofern die übergebene Klasse Längengrenzen deklariert
     * und der Wert außerhalb dieser Grenzen liegt. Werte, die keine Skalare oder
     * BackedEnum Instanzen sind (z.B. DateTimeInterface), werden übersprungen.
     *
     * @param class-string $className
     */
    protected function assertStringLength(string $className, mixed $value): void
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (is_int($value) === false && is_float($value) === false && is_string($value) === false) {
            return;
        }

        ['min' => $min, 'max' => $max] = $this->getLengthBounds($className);

        if ($min === null && $max === null) {
            return;
        }

        $stringValue = strval($value);
        $length = mb_strlen($stringValue, 'UTF-8');

        if ($min !== null && $length < $min) {
            throw new RuntimeException(sprintf(
                'Value %s is shorter than minLength %d (length %d).',
                $this->quoteLengthValue($stringValue),
                $min,
                $length
            ));
        }

        if ($max !== null && $length > $max) {
            throw new RuntimeException(sprintf(
                'Value %s is longer than maxLength %d (length %d).',
                $this->quoteLengthValue($stringValue),
                $max,
                $length
            ));
        }
    }

    /**
     * Setzt den übergebenen Wert in einfache Anführungszeichen.
     */
    private function quoteLengthValue(string $value): string
    {
        return "'" . $value . "'";
    }
}
