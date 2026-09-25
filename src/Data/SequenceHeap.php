<?php

declare(strict_types=1);

namespace MMNewmedia\Data;

use MMNewmedia\Xsd\Sequence;
use ReflectionAttribute;
use SplMinHeap;

/**
 * SPL Min Heap Implementierung zur Darstellung der richtigen Reihenfolge von Elementen
 * Die Reihenfolge wird durch XSD Sequence Blöcke vorgegeben
 * 
 * @author Marcel Maaß <marcel@mm-newmedia.de>
 * @since 2025-01-03
 */

class SequenceHeap extends SplMinHeap
{
    /**
     * {@inheritDoc}
     * @see SplMinHead::compare()
     */
    protected function compare(mixed $elementA, mixed $elementB): int
    {
        $attributes = $elementA->getAttributes(Sequence::class);
        $sequenceA = reset($attributes);

        $attributes = $elementB->getAttributes(Sequence::class);
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
     * @param ReflectionAttribute $attribute
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