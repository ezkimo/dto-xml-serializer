<?php

declare(strict_types=1);

namespace MMNewmedia\Serializer;

use BackedEnum;
use ArgumentCountError;
use DateTimeImmutable;
use DateTimeInterface;
use JsonException;
use Laminas\Serializer\Adapter\AdapterInterface;
use MMNewmedia\Model\CrossIndustryInvoice\CrossIndustryInvoiceType;
use MMNewmedia\Model\UniversalBusinessLanguage\Invoice\InvoiceType;
use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Xsd\Attribute;
use MMNewmedia\Xsd\Element;
use MMNewmedia\Xsd\Extension;
use MMNewmedia\Xsd\Sequence;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Reflector;
use RuntimeException;
use SplObjectStorage;

/**
 * Serialisiert DTOs in eine JSON Repräsentation und erzeugt aus einer JSON Repräsentation wieder DTOs.
 *
 * Die JSON Struktur orientiert sich an den XSD Element Namen: Jede Eigenschaft, die mit dem
 * Element Attribut annotiert ist, wird als Key mit dem XSD Element Namen abgebildet. Simple
 * Content Typen werden als skalare Werte serialisiert. Besitzt ein Simple Content Typ Attribute,
 * wird die Wertmenge um den key "value" für den eigentlichen Inhalt erweitert.
 */
class JsonDtoSerializer implements AdapterInterface
{
    /**
     * Zuordnung des JSON Wurzelelementnamens zu der fqcn des DTOs.
     *
     * @var array<string, string>
     */
    protected array $rootClasses = [
        'Invoice' => InvoiceType::class,
        'CrossIndustryInvoice' => CrossIndustryInvoiceType::class,
    ];

    #[Override]
    public function serialize(mixed $value): string
    {
        if (!is_object($value)) {
            throw new RuntimeException('Expected an object to serialize.');
        }

        return $this->dtoToJson($value);
    }

    #[Override]
    public function unserialize(string $serialized): mixed
    {
        return $this->jsonToDto($serialized);
    }

    /**
     * Registriert eine Zuordnung von Wurzelelementnamen zu einer DTO Klasse.
     */
    public function setRootClasses(array $rootClasses): void
    {
        $this->rootClasses = $rootClasses;
    }

    /**
     * Wandelt ein DTO Objekt in eine JSON Repräsentation um.
     */
    protected function dtoToJson(object $dto): string
    {
        $reflector = new ReflectionClass($dto);

        $json = json_encode(
            [$this->getRootElementName($reflector) => $this->objectToArray($dto)],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        return $json;
    }

    /**
     * Wandelt eine JSON Repräsentation in ein DTO Objekt um.
     */
    protected function jsonToDto(string $json): object
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Invalid JSON: ' . $exception->getMessage(), 0, $exception);
        }

        if (!is_array($data) || count($data) !== 1) {
            throw new RuntimeException('JSON must contain exactly one root element.');
        }

        $rootName = (string) array_key_first($data);

        if (!isset($this->rootClasses[$rootName])) {
            throw new RuntimeException(sprintf('Unknown root element "%s".', $rootName));
        }

        $root = $this->arrayToObject($this->rootClasses[$rootName], $data[$rootName]);
        if (!is_object($root)) {
            throw new RuntimeException(sprintf('Root element "%s" does not map to an object.', $rootName));
        }

        return $root;
    }

    /**
     * Liefert den Namen des JSON Wurzelelements für die übergebene DTO Klasse.
     */
    protected function getRootElementName(ReflectionClass $reflector): string
    {
        $name = array_search($reflector->getName(), $this->rootClasses, true);
        if ($name !== false) {
            return (string) $name;
        }

        $element = $this->getAttribute($reflector, Element::class);
        if ($element !== null) {
            $name = $element->newInstance()->name;
            if ($name !== null) {
                return $name;
            }
        }

        throw new RuntimeException(sprintf('No root element name configured for class "%s".', $reflector->getName()));
    }

    /**
     * Liefert das erste Vorkommen des übergebenen Attributs an der übergebenen Stelle.
     */
    protected function getAttribute(Reflector $reflector, string $attribute): ?ReflectionAttribute
    {
        $attributes = $reflector->getAttributes($attribute);

        return $attributes[0] ?? null;
    }

    /**
     * Liefert die Namen der übergebenen Klasse, die das Element Attribut besitzen. Die Reihenfolge
     * folgt den XSD Sequence Positionen, Eigenschaften ohne Sequence werden an den Anfang gesetzt.
     *
     * @return ReflectionProperty[]
     */
    protected function getElementProperties(ReflectionClass $reflector): array
    {
        $properties = [];
        $index = 0;

        foreach ($reflector->getProperties() as $property) {
            if ($this->getAttribute($property, Element::class) === null) {
                continue;
            }

            if (isset($properties[$property->getName()])) {
                continue;
            }

            $properties[$property->getName()] = [$property, $this->getSequencePosition($property), $index++];
        }

        $properties = array_values($properties);
        usort($properties, fn(array $a, array $b): int => $a[1] <=> $b[1] ?: $a[2] <=> $b[2]);

        return array_column($properties, 0);
    }

    /**
     * Liefert die XSD Sequence Position der übergebenen Eigenschaft.
     */
    protected function getSequencePosition(ReflectionProperty $property): int
    {
        $sequence = $this->getAttribute($property, Sequence::class);
        if ($sequence === null) {
            return 0;
        }

        $arguments = $sequence->getArguments();

        return (int) ($arguments['position'] ?? 0);
    }

    /**
     * Liefert die Eigenschaften der übergebenen Klasse (inklusive der geerbten), die das Attribute
     * Attribut besitzen.
     *
     * @return ReflectionProperty[]
     */
    protected function getAttributeProperties(string $className): array
    {
        $properties = [];
        $reflector = new ReflectionClass($className);

        do {
            foreach ($reflector->getProperties() as $property) {
                if ($this->getAttribute($property, Attribute::class) === null) {
                    continue;
                }

                $properties[$property->getName()] = $property;
            }
        } while ($reflector = $reflector->getParentClass());

        return array_values($properties);
    }

    /**
     * Wandelt ein DTO Objekt rekursiv in ein assoziatives Array um.
     */
    protected function objectToArray(object $dto): array
    {
        $result = [];
        $reflector = new ReflectionClass($dto);

        foreach ($this->getElementProperties($reflector) as $property) {
            $value = $property->getValue($dto);
            if ($value === null || ($value instanceof SplObjectStorage && $value->count() === 0)) {
                continue;
            }

            $element = $this->getAttribute($property, Element::class);
            if ($element === null) {
                continue;
            }

            $name = $this->getElementName($property, $element);

            if ($value instanceof SplObjectStorage) {
                $result[$name] = [];
                foreach ($value as $instance) {
                    $result[$name][] = $this->valueToArray($instance, $element);
                }
                continue;
            }

            $result[$name] = $this->valueToArray($value, $element);
        }

        return $result;
    }

    /**
     * Wandelt einen Eigenschaftswert in seine JSON Repräsentation um.
     */
    protected function valueToArray(mixed $value, ReflectionAttribute $element): mixed
    {
        if ($value instanceof ValueableInterface) {
            return $this->leafToArray($value);
        }

        if ($value instanceof DateTimeInterface) {
            return $this->formatDateTime($value, $element->newInstance()->type);
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (is_object($value)) {
            return $this->objectToArray($value);
        }

        return $value;
    }

    /**
     * Wandelt einen Simple Content Typ in seine JSON Repräsentation um. Besitzt der Typ Attribute,
     * werden diese als eigene Keys abgebildet und der Inhalt unter dem key "value" abgelegt.
     */
    protected function leafToArray(object $leaf): mixed
    {
        $result = [];

        foreach ($this->getAttributeProperties($leaf::class) as $property) {
            $attribute = $this->getAttribute($property, Attribute::class);
            $attributeValue = $property->getValue($leaf);

            if ($attribute === null || $attributeValue === null) {
                continue;
            }

            $result[$this->getAttributeName($property, $attribute)] = $attributeValue;
        }

        $value = $this->normalizeValue($leaf->getValue(), $leaf::class);

        if ($result !== []) {
            if ($value !== null) {
                $result['value'] = $value;
            }

            return $result;
        }

        return $value;
    }

    /**
     * Normalisiert den Wert eines Simple Content Typs für die JSON Ausgabe.
     */
    protected function normalizeValue(mixed $value, string $className): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $this->formatDateTime($value, $this->getExtensionBase($className));
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    /**
     * Formatiert einen DateTime Wert abhängig vom zugrunde liegenden XSD Typ.
     */
    protected function formatDateTime(DateTimeInterface $value, ?string $type): string
    {
        return match ($type) {
            'xsd:dateTime' => $value->format('Y-m-d\TH:i:sP'),
            default => $value->format('Y-m-d'),
        };
    }

    /**
     * Liefert den Namen einer XML/JSON Eigenschaft aus dem Attribute Attribut.
     */
    protected function getAttributeName(ReflectionProperty $property, ReflectionAttribute $attribute): string
    {
        return $attribute->newInstance()->name ?? $property->getName();
    }

    /**
     * Liefert den Namen eines Elements aus dem Element Attribut. Liefert das Attribut keinen Namen,
     * wird der Name der ReflectionProperty Instanz genommen.
     */
    protected function getElementName(ReflectionProperty $property, ReflectionAttribute $attribute): string
    {
        return $attribute->newInstance()->name ?? $property->getName();
    }

    /**
     * Liefert den Elementtyp einer Eigenschaft aus dem Element Attribut bzw. der Eigenschaftstypisierung.
     */
    protected function getElementType(ReflectionProperty $property, ReflectionAttribute $element): string
    {
        $type = $element->newInstance()->type;

        if (is_string($type) && $type !== '') {
            return $type;
        }

        $propertyType = $property->getType();
        if ($propertyType instanceof ReflectionNamedType) {
            return $propertyType->getName();
        }

        throw new RuntimeException(sprintf('Unable to determine element type for property "%s".', $property->getName()));
    }

    /**
     * Prüft, ob die übergebene Eigenschaft eine Collection (SplObjectStorage) ist.
     */
    protected function isCollection(ReflectionProperty $property): bool
    {
        $type = $property->getType();

        return $type instanceof ReflectionNamedType && $type->getName() === SplObjectStorage::class;
    }

    /**
     * Wandelt einen JSON Wert in das Objekt der übergebenen Klasse um.
     */
    protected function arrayToObject(string $className, mixed $data): mixed
    {
        if (!class_exists($className)) {
            return $this->convertScalar($data, $className);
        }

        if (is_a($className, ValueableInterface::class, true)) {
            return $this->arrayToValueable($className, $data);
        }

        if (!is_array($data)) {
            throw new RuntimeException(sprintf(
                'Expected an object for element of class "%s", got %s.',
                $className,
                get_debug_type($data)
            ));
        }

        return $this->arrayToComplex($className, $data);
    }

    /**
     * Erzeugt ein komplexes DTO aus einem assoziativen Array.
     */
    protected function arrayToComplex(string $className, array $data): object
    {
        $reflector = new ReflectionClass($className);
        $arguments = [];
        $knownKeys = [];

        foreach ($this->getElementProperties($reflector) as $property) {
            $element = $this->getAttribute($property, Element::class);
            if ($element === null) {
                continue;
            }

            $name = $this->getElementName($property, $element);
            $knownKeys[$name] = true;

            if (!array_key_exists($name, $data) || $data[$name] === null) {
                continue;
            }

            $raw = $data[$name];

            if ($this->isCollection($property)) {
                if (is_array($raw) === false || array_is_list($raw) === false) {
                    $raw = [$raw];
                }

                $collection = new SplObjectStorage();
                foreach ($raw as $item) {
                    $collection->offsetSet($this->arrayToObject($this->getElementType($property, $element), $item));
                }

                $arguments[$property->getName()] = $collection;
                continue;
            }

            $value = $this->arrayToObject($this->getElementType($property, $element), $raw);
            $arguments[$property->getName()] = $this->coerceToPropertyType($value, $property);
        }

        $this->assertNoUnknownKeys($data, $knownKeys, $className);

        try {
            return $reflector->newInstanceArgs($arguments);
        } catch (ArgumentCountError $exception) {
            throw new RuntimeException(sprintf(
                'Cannot create "%s": missing required element(s) "%s".',
                $className,
                implode('", "', $this->getMissingRequiredArguments($reflector, $arguments))
            ), 0, $exception);
        }
    }

    /**
     * Liefert die Namen der Konstruktorparameter, die weder übergeben noch mit einem Defaultwert
     * versehen sind.
     */
    protected function getMissingRequiredArguments(ReflectionClass $reflector, array $arguments): array
    {
        $missing = [];
        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return $missing;
        }

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                continue;
            }

            if (!array_key_exists($parameter->getName(), $arguments)) {
                $missing[] = $parameter->getName();
            }
        }

        return $missing;
    }

    /**
     * Erzeugt einen Simple Content Typ aus einem skalaren Wert bzw. einem Array mit Attributen.
     */
    protected function arrayToValueable(string $className, mixed $data): object
    {
        if (is_array($data)) {
            $value = array_key_exists('value', $data) ? $data['value'] : null;
            $attributeData = $data;
            unset($attributeData['value']);
        } else {
            $value = $data;
            $attributeData = [];
        }

        $attributeProperties = $this->getAttributeProperties($className);
        $arguments = [];
        $knownKeys = [];

        foreach ($attributeProperties as $property) {
            $attribute = $this->getAttribute($property, Attribute::class);
            $name = $this->getAttributeName($property, $attribute);
            $knownKeys[$name] = true;

            if (!array_key_exists($name, $attributeData)) {
                continue;
            }

            $arguments[$property->getName()] = $attributeData[$name];
        }

        $this->assertNoUnknownKeys($attributeData, $knownKeys, $className);

        $reflector = new ReflectionClass($className);
        $object = $reflector->newInstanceArgs($this->completeRequiredArguments($reflector, $arguments));

        $converted = $this->convertValue($value, $className);
        if ($converted !== null) {
            $object->setValue($converted);
        }

        return $object;
    }

    /**
     * Ergänzt fehlende, verpflichtende Konstruktorargumente (z.B. Attribute von Simple Content Typen)
     * mit null, sofern der Parameter nullable ist.
     */
    protected function completeRequiredArguments(ReflectionClass $reflector, array $arguments): array
    {
        $constructor = $reflector->getConstructor();
        if ($constructor === null) {
            return $arguments;
        }

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $arguments)) {
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                continue;
            }

            if ($parameter->allowsNull()) {
                $arguments[$name] = null;
            }
        }

        return $arguments;
    }

    /**
     * Wirft eine Exception, sofern das Array Keys enthält, die keinem Element/Attribut entsprechen.
     */
    protected function assertNoUnknownKeys(array $data, array $knownKeys, string $className): void
    {
        $unknown = array_diff_key($data, $knownKeys);

        if ($unknown !== []) {
            throw new RuntimeException(sprintf(
                'Unknown element(s) "%s" for class "%s".',
                implode('", "', array_keys($unknown)),
                $className
            ));
        }
    }

    /**
     * Wandelt den Wert eines Simple Content Typs abhängig vom zugrunde liegenden XSD Typ.
     */
    protected function convertValue(mixed $value, string $className): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        $base = $this->getExtensionBase($className);

        return match ($base) {
            'xsd:date', 'xsd:dateTime' => $value === null ? null : new DateTimeImmutable((string) $value),
            'xsd:boolean' => is_string($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : (bool) $value,
            default => $value,
        };
    }

    /**
     * Wandelt einen Wert für XSD Skalartypen, die keiner DTO Klasse entsprechen.
     */
    protected function convertScalar(mixed $value, string $type): mixed
    {
        return match ($type) {
            'xsd:date', 'xsd:dateTime' => $value instanceof DateTimeInterface
                ? $value
                : new DateTimeImmutable((string) $value),
            'xsd:boolean' => is_string($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : (bool) $value,
            default => $value,
        };
    }

    /**
     * Liefert den XSD Basistyp des übergebenen Simple Content Typs. Es wird der komplette
     * Vererbungsbaum durchlaufen.
     */
    protected function getExtensionBase(string $className): ?string
    {
        $reflector = new ReflectionClass($className);

        do {
            $extension = $this->getAttribute($reflector, Extension::class);
            if ($extension !== null) {
                return $extension->newInstance()->getBase();
            }
        } while ($reflector = $reflector->getParentClass());

        return null;
    }

    /**
     * Passt einen Wert an den deklarierten PHP Typ der übergebenen Eigenschaft an.
     */
    protected function coerceToPropertyType(mixed $value, ReflectionProperty $property): mixed
    {
        $type = $property->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin() === false) {
            return $value;
        }

        return match ($type->getName()) {
            'string' => is_string($value) ? $value : (string) $value,
            'int' => is_int($value) ? $value : (int) $value,
            'float' => is_float($value) ? $value : (float) $value,
            'bool' => is_bool($value) ? $value : (bool) $value,
            default => $value,
        };
    }
}
