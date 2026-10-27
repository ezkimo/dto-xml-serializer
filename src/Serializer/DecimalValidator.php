<?php

declare(strict_types=1);

namespace MMNewmedia\Serializer;

use BackedEnum;
use MMNewmedia\Xsd\FractionDigits;
use MMNewmedia\Xsd\MaxInclusive;
use MMNewmedia\Xsd\MinExclusive;
use MMNewmedia\Xsd\MinInclusive;
use ReflectionClass;
use RuntimeException;

/**
 * Ermittelt Xsd\FractionDigits/Xsd\MaxInclusive/Xsd\MinExclusive/Xsd\MinInclusive Deklarationen und prüft
 * Werte gegen die deklarierten Dezimalbeschränkungen.
 *
 * Die Traits werden von den Serializern verwendet, um simple Content Werte vor dem
 * Serialisieren bzw. nach dem Deserialisieren gegen die Dezimalbeschränkungen zu validieren.
 *
 * @psalm-type DecimalParts = array{sign: int, digits: string, scale: int}
 * @psalm-type DecimalBound = array{literal: string, decimal: DecimalParts}
 */
trait DecimalValidator
{
    /**
     * Liefert die striktesten Dezimalbeschränkungen im Vererbungsbaum der übergebenen Klasse.
     *
     * Mehrere Restriction-Stufen werden wie in XSD als Schnittmenge behandelt:
     * die kleinste fractionDigits, die kleinste maxInclusive, die größte minExclusive
     * und die größte minInclusive gewinnen.
     *
     * @param class-string $className
     * @return array{
     *     fractionDigits: null|int,
     *     maxInclusive: null|DecimalBound,
     *     minExclusive: null|DecimalBound,
     *     minInclusive: null|DecimalBound,
     * }
     */
    protected function getDecimalFacets(string $className): array
    {
        $fractionDigits = null;
        $maxInclusive = null;
        $minExclusive = null;
        $minInclusive = null;
        $reflector = new ReflectionClass($className);

        do {
            $attribute = $reflector->getAttributes(FractionDigits::class)[0] ?? null;
            if ($attribute !== null) {
                $value = $attribute->newInstance()->value;
                $fractionDigits = $fractionDigits === null ? $value : min($fractionDigits, $value);
            }

            $attribute = $reflector->getAttributes(MaxInclusive::class)[0] ?? null;
            if ($attribute !== null) {
                $value = $attribute->newInstance()->value;
                $decimal = $this->normalizeDecimal($value);

                if ($decimal === null) {
                    throw new RuntimeException(sprintf(
                        'Invalid decimal value %s for maxInclusive.',
                        $this->quoteDecimalValue($value)
                    ));
                }

                if ($maxInclusive === null || $this->compareDecimals($decimal, $maxInclusive['decimal']) < 0) {
                    $maxInclusive = [
                        'literal' => $this->formatDecimalLiteral($value),
                        'decimal' => $decimal,
                    ];
                }
            }

            $attribute = $reflector->getAttributes(MinExclusive::class)[0] ?? null;
            if ($attribute !== null) {
                $value = $attribute->newInstance()->value;
                $decimal = $this->normalizeDecimal($value);

                if ($decimal === null) {
                    throw new RuntimeException(sprintf(
                        'Invalid decimal value %s for minExclusive.',
                        $this->quoteDecimalValue($value)
                    ));
                }

                if ($minExclusive === null || $this->compareDecimals($decimal, $minExclusive['decimal']) > 0) {
                    $minExclusive = [
                        'literal' => $this->formatDecimalLiteral($value),
                        'decimal' => $decimal,
                    ];
                }
            }

            $attribute = $reflector->getAttributes(MinInclusive::class)[0] ?? null;
            if ($attribute !== null) {
                $value = $attribute->newInstance()->value;
                $decimal = $this->normalizeDecimal($value);

                if ($decimal === null) {
                    throw new RuntimeException(sprintf(
                        'Invalid decimal value %s for minInclusive.',
                        $this->quoteDecimalValue($value)
                    ));
                }

                if ($minInclusive === null || $this->compareDecimals($decimal, $minInclusive['decimal']) > 0) {
                    $minInclusive = [
                        'literal' => $this->formatDecimalLiteral($value),
                        'decimal' => $decimal,
                    ];
                }
            }
        } while ($reflector = $reflector->getParentClass());

        return [
            'fractionDigits' => $fractionDigits,
            'maxInclusive' => $maxInclusive,
            'minExclusive' => $minExclusive,
            'minInclusive' => $minInclusive,
        ];
    }

    /**
     * Wirft eine RuntimeException, sofern die übergebene Klasse Dezimalbeschränkungen
     * deklariert und der Wert gegen eine dieser Beschränkungen verstößt. Werte, die
     * keine Skalare oder BackedEnum Instanzen sind (z.B. DateTimeInterface), werden übersprungen.
     *
     * @param class-string $className
     */
    protected function assertDecimalConstraints(string $className, mixed $value): void
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (is_int($value) === false && is_float($value) === false && is_string($value) === false) {
            return;
        }

        $facets = $this->getDecimalFacets($className);

        if (
            $facets['fractionDigits'] === null
            && $facets['maxInclusive'] === null
            && $facets['minExclusive'] === null
            && $facets['minInclusive'] === null
        ) {
            return;
        }

        $decimal = $this->normalizeDecimal($value);

        if ($decimal === null) {
            throw new RuntimeException(sprintf(
                'Value %s is not a valid decimal.',
                $this->quoteDecimalValue($value)
            ));
        }

        $fractionDigits = $facets['fractionDigits'];

        if ($fractionDigits !== null) {
            $actual = $this->countFractionDigits($decimal);

            if ($actual > $fractionDigits) {
                throw new RuntimeException(sprintf(
                    'Value %s has more than %d fraction digits (%d).',
                    $this->quoteDecimalValue($value),
                    $fractionDigits,
                    $actual
                ));
            }
        }

        $maxInclusive = $facets['maxInclusive'];

        if ($maxInclusive !== null && $this->compareDecimals($decimal, $maxInclusive['decimal']) > 0) {
            throw new RuntimeException(sprintf(
                'Value %s is greater than maxInclusive %s.',
                $this->quoteDecimalValue($value),
                $maxInclusive['literal']
            ));
        }

        $minExclusive = $facets['minExclusive'];

        if ($minExclusive !== null && $this->compareDecimals($decimal, $minExclusive['decimal']) <= 0) {
            throw new RuntimeException(sprintf(
                'Value %s is not greater than minExclusive %s.',
                $this->quoteDecimalValue($value),
                $minExclusive['literal']
            ));
        }

        $minInclusive = $facets['minInclusive'];

        if ($minInclusive !== null && $this->compareDecimals($decimal, $minInclusive['decimal']) < 0) {
            throw new RuntimeException(sprintf(
                'Value %s is less than minInclusive %s.',
                $this->quoteDecimalValue($value),
                $minInclusive['literal']
            ));
        }
    }

    /**
     * Normalisiert einen Dezimalwert zu Vorzeichen, signifikanten Ziffern und Skala.
     *
     * Der Wert wird als digits * 10^-scale dargestellt. Führende Nullen der Ziffern
     * sowie Nullen am Ende des Bruchteils werden entfernt; dadurch entspricht die
     * Anzahl der Bruchstellen der XSD-Semantik von fractionDigits.
     *
     * @return null|DecimalParts
     */
    private function normalizeDecimal(int|float|string $value): ?array
    {
        if (is_float($value) && is_finite($value) === false) {
            return null;
        }

        $literal = is_string($value) ? trim($value) : strval($value);

        if (preg_match('/^([+-]?)(?=\d|\.\d)(\d*)(?:\.(\d*))?(?:[eE]([+-]?\d+))?$/', $literal, $matches) !== 1) {
            return null;
        }

        $exponent = 0;
        $exponentLiteral = $matches[4] ?? '';

        if ($exponentLiteral !== '') {
            $exponentDigits = ltrim($exponentLiteral, '+-');

            if (strlen(ltrim($exponentDigits, '0')) > 9) {
                return null;
            }

            $exponent = (int) $exponentLiteral;
        }

        $integer = $matches[2] ?? '';
        $fraction = $matches[3] ?? '';
        $digits = ltrim($integer . $fraction, '0');
        $scale = strlen($fraction) - $exponent;

        if ($digits === '') {
            return ['sign' => 0, 'digits' => '0', 'scale' => 0];
        }

        $trailingZeros = strlen($digits) - strlen(rtrim($digits, '0'));
        $digits = rtrim($digits, '0');
        $scale -= $trailingZeros;

        return [
            'sign' => ($matches[1] ?? '') === '-' ? -1 : 1,
            'digits' => $digits,
            'scale' => $scale,
        ];
    }

    /**
     * Vergleicht zwei normalisierte Dezimalwerte ohne Gleitkomma-Arithmetik.
     *
     * @param DecimalParts $a
     * @param DecimalParts $b
     */
    private function compareDecimals(array $a, array $b): int
    {
        if ($a['sign'] !== $b['sign']) {
            return $a['sign'] <=> $b['sign'];
        }

        if ($a['sign'] === 0) {
            return 0;
        }

        $lengthComparison = (strlen($a['digits']) - $a['scale']) <=> (strlen($b['digits']) - $b['scale']);

        if ($lengthComparison !== 0) {
            return $a['sign'] === 1 ? $lengthComparison : -$lengthComparison;
        }

        $length = max(strlen($a['digits']), strlen($b['digits']));
        $comparison = strcmp(
            str_pad($a['digits'], $length, '0'),
            str_pad($b['digits'], $length, '0')
        ) <=> 0;

        return $a['sign'] === 1 ? $comparison : -$comparison;
    }

    /**
     * Liefert die Anzahl der Bruchstellen nach dem Entfernen nachgestellter Nullen.
     *
     * @param DecimalParts $decimal
     */
    private function countFractionDigits(array $decimal): int
    {
        return max(0, $decimal['scale']);
    }

    private function formatDecimalLiteral(int|float|string $value): string
    {
        return is_string($value) ? $value : strval($value);
    }

    /**
     * Setzt den übergebenen Wert in einfache Anführungszeichen.
     */
    private function quoteDecimalValue(int|float|string $value): string
    {
        return "'" . $this->formatDecimalLiteral($value) . "'";
    }
}
