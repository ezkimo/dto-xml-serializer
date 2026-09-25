<?php

declare(strict_types=1);

namespace MMNewmedia\Data;

use MMNewmedia\Xsd\Sequence;
use ReflectionAttribute;
use ReflectionProperty;
use SplMinHeap;

/**
 * SPL Min Heap Implementierung zur Darstellung der richtigen Reihenfolge von Elementen
 * Die Reihenfolge wird durch XSD Sequence Blöcke vorgegeben
 *
 * @author Marcel Maaß <marcel@mm-newmedia.de>
 * @since 2025-01-03
 *
 * @extends SplMinHeap<ReflectionProperty>
 */

class SequenceHeap extends SplMinHeap
{
    /**
     * {@inheritDoc}
     * @see SplMinHead::compare()
     */
    #[\Override]
    protected function compare(mixed $value1, mixed $value2): int
    {
        $attributes = $value1->getAttributes(Sequence::class);
        $sequenceA = reset($attributes);

        $attributes = $value2->getAttributes(Sequence::class);
        $sequenceB = reset($attributes);

        $valueA = $this->getCurrentPosition($sequenceA);
        $valueB = $this->getCurrentPosition($sequenceB);

        if ($valueA === $valueB) {
            return 0;
        }

        return $valueA > $valueB ? -1 : 1;
    }

    /**
     * Liefert den Wert des position Arguments des übergebenen Sequence Attributes
     *
     * @param ReflectionAttribute<Sequence>|false $attribute
     * @return false|int
     */
    public function getCurrentPosition(false|ReflectionAttribute $attribute): false|int
    {
        $value = 0;
        if ($attribute !== false) {
            $value = current(
                array_filter(
                    $attribute->getArguments(), 
                    fn($key) => $key === 'position', 
                    ARRAY_FILTER_USE_KEY
                )
            );
        }
        
        return $value;
    }
}