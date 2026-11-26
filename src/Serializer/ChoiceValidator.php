<?php

declare(strict_types=1);

namespace MMNewmedia\Serializer;

use MMNewmedia\Xsd\Choice;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use SplObjectStorage;

/**
 * Ermittelt Xsd\Choice Deklarationen und prüft, dass pro Gruppe die deklarierte
 * Anzahl an Alternativen gesetzt ist. Ein null Wert markiert eine nicht gewählte
 * Alternative, eine leere SplObjectStorage wird ebenfalls als nicht gewählt behandelt.
 */
trait ChoiceValidator
{
    /**
     * Liefert alle Choice Gruppen des DTO inklusive der geerbten Eigenschaften.
     *
     * @return array<string, array{properties: ReflectionProperty[], min: int, max: int}>
     */
    protected function getChoiceGroups(object $dto): array
    {
        $reflector = new ReflectionClass($dto);
        $groups = [];
        $seen = [];

        do {
            foreach ($reflector->getProperties() as $property) {
                if (isset($seen[$property->getName()])) {
                    continue;
                }
                $seen[$property->getName()] = true;

                $attribute = $property->getAttributes(Choice::class)[0] ?? null;
                if ($attribute === null) {
                    continue;
                }

                $choice = $attribute->newInstance();
                $name = $choice->group ?? $reflector->getName();
                $min = $choice->minOccurs ?? 1;
                $max = $choice->maxOccurs ?? 1;

                if ($min < 0 || $max < 0 || $min > $max) {
                    throw new RuntimeException(sprintf(
                        'Invalid choice group "%s": minOccurs %d must not exceed maxOccurs %d.',
                        $name,
                        $min,
                        $max
                    ));
                }

                if (isset($groups[$name])) {
                    if ($groups[$name]['min'] !== $min || $groups[$name]['max'] !== $max) {
                        throw new RuntimeException(sprintf(
                            'Inconsistent minOccurs/maxOccurs for choice group "%s".',
                            $name
                        ));
                    }

                    $groups[$name]['properties'][] = $property;
                    continue;
                }

                $groups[$name] = [
                    'properties' => [$property],
                    'min' => $min,
                    'max' => $max,
                ];
            }
        } while ($reflector = $reflector->getParentClass());

        return $groups;
    }

    /**
     * Wirft eine RuntimeException, sofern eine Choice Gruppe nicht die deklarierte
     * Anzahl an gesetzten Alternativen enthält.
     */
    protected function assertChoiceGroups(object $dto): void
    {
        foreach ($this->getChoiceGroups($dto) as $name => ['properties' => $properties, 'min' => $min, 'max' => $max]) {
            $present = 0;

            foreach ($properties as $property) {
                $value = $property->getValue($dto);
                if ($value === null || ($value instanceof SplObjectStorage && $value->count() === 0)) {
                    continue;
                }

                $present++;
            }

            if ($min === 1 && $max === 1) {
                if ($present !== 1) {
                    throw new RuntimeException(sprintf(
                        'Choice group "%s" requires exactly one value, got %d.',
                        $name,
                        $present
                    ));
                }

                continue;
            }

            if ($present < $min) {
                throw new RuntimeException(sprintf(
                    'Choice group "%s" requires at least %d value(s), got %d.',
                    $name,
                    $min,
                    $present
                ));
            }

            if ($present > $max) {
                if ($max === 1) {
                    throw new RuntimeException(sprintf(
                        'Choice group "%s" allows at most one value, got %d.',
                        $name,
                        $present
                    ));
                }

                throw new RuntimeException(sprintf(
                    'Choice group "%s" allows at most %d value(s), got %d.',
                    $name,
                    $max,
                    $present
                ));
            }
        }
    }
}
