<?php

declare(strict_types=1);

namespace MMNewmedia\Serializer;

use BackedEnum;
use DateTimeInterface;
use DOMDocument;
use DOMElement;
use Laminas\Serializer\Adapter\AdapterInterface;
use MMNewmedia\Data\SequenceHeap;
use MMNewmedia\Model\ValueableInterface;
use MMNewmedia\Xsd\Attribute;
use MMNewmedia\Xsd\ComplexType;
use MMNewmedia\Xsd\Element;
use MMNewmedia\Xsd\Extension;
use MMNewmedia\Xsd\RootElement;
use MMNewmedia\Xsd\Schema;
use MMNewmedia\Xsd\Sequence;
use MMNewmedia\Xsd\SimpleContent;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use SplObjectStorage;

class XmlDtoSerializer implements AdapterInterface
{
    /**
     * Mappgin von Namespace URI zu Namespace Prefixes
     * @var array
     */
    protected array $namespaceMapping = [];

    /**
     * fqcn der zu Namespace Enumeration
     * @var string
     */
    protected string $namespaces;

    /**
     * Ob für Kindelemente die im Element-Attribut deklarierten Namespaces den Vorrang vor der
     * targetNamespace des Schema-Attributs der Elternklasse haben.
     *
     * Das CII Modell serialisiert alle Kindelemente eines komplexen Typs in dessen
     * Schema targetNamespace (Element-Deklarationen sind dort unzuverlässig). Das UBL Modell
     * deklariert den Namespace dagegen je Kindelement im Element-Attribut.
     *
     * @var bool
     */
    protected bool $usePropertyNamespaces = false;

    #[Override]
    public function serialize(mixed $value): string
    {
        return $this->dtoToXml($value);
    }

    #[Override]
    public function unserialize(string $serialized): mixed
    {
        return $this->xmlToDto($serialized);
    }

    /**
     * Wandelt ein DTO Objekt in eine XML Repräsentation um. Dabei wird die Klasse des DTOs auf das 
     * Vorhandensein des Element-Attributs überprüft, um den Namen und Namespace des Wurzelelements zu ermitteln. Anschließend 
     * wird ein DOMDocument erstellt und die serializeObject Methode aufgerufen, um das DTO rekursiv in XML Knoten umzuwandeln. 
     * Am Ende wird die XML Repräsentation als String zurückgegeben.
     * 
     * @param object $dto Das zu serialisierende DTO Objekt
     * @return string Die XML Repräsentation des DTOs
     */
    protected function dtoToXml(object $dto): string
    {
        $reflector = new ReflectionClass($dto);
        $rootAttribute = $reflector->getAttributes(RootElement::class)[0] ?? false;

        if ($rootAttribute === false) {
            throw new RuntimeException('Missing #[RootElement] attribute');
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        
        /** @var \ReflectionAttribute<\MMNewmedia\Xsd\RootElement> $rootAttribute */ 
        $rootName = $rootAttribute->newInstance()->name;
        $rootNamespace = $this->getElementNamespace($reflector, $rootAttribute);

        $qualifiedRootName = $this->getElementQualifiedName($rootName, $rootNamespace);
        $rootNode = $dom->createElementNs($rootNamespace, $qualifiedRootName);
        
        $namespaces = $this->getNamespaceMapping();
        foreach ($namespaces as $namespace => $prefix) {
            $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . strtolower($prefix), $namespace);
        }

        $dom->appendChild($rootNode);
        $this->serializeObject($dto, $rootNode, $dom);

        return $dom->saveXML();
    }

    protected function xmlToDto(string $xml): string
    {
        return '';
    }

    /**
     * Ordnet die Eigenschaften eines Objekts nach XML Sequence Positionen
     * 
     * @param array $properties
     * @return SequenceHeap
     */
    protected function generateSequenceHeap(array $properties): SequenceHeap
    {
        $sequenceHeap = new SequenceHeap();
        $sequences = array_filter($properties, fn($property) => $property->getAttributes(Sequence::class) !== []);
        foreach ($sequences as $element) {
            $sequenceHeap->insert($element);
        }

        return $sequenceHeap;
    }

    /**
     * Liefert alle Eigenschaften der übergebenen Klasse, die das Attribute Attribut besitzen
     * 
     * @param string $className
     * @return ReflectionProperty[]
     */
    public function getElementAttributes(string $className): array
    {
        $attributes = [];
        $reflector = new ReflectionClass($className);
        
        $properties = $reflector->getProperties();
        $attributes = array_filter(
            $properties, 
            fn(ReflectionProperty $property) => $property->getAttributes(Attribute::class) !== []
        );

        return $attributes;
    }

    /**
     * Liefert den Namen eines Elements aus dem Element-Attribut zu ermitteln. Liefert das Attribut
     * keinen Namen, wird der Name der ReflectionProperty Instanz genommen.
     *
     * @param ReflectionProperty $element
     * @param ReflectionAttribute<Element|RootElement> $attribute
     * @return string
     */
    protected function getElementName(ReflectionProperty $element, ReflectionAttribute $attribute): string
    {
        $name = null;
        $elementAttribute = $attribute->newInstance();

        if ($elementAttribute->name !== null) {
            $name = $elementAttribute->name;
        }

        if ($name === null) {
            $name = $element->getName();
        }

        return $name;
    }

    /**
     * Liefert den Namespace des übergebenen Elements.
     *
     * @param ReflectionClass|ReflectionProperty $element
     * @param ReflectionAttribute<Element|RootElement> $attribute
     * @return null|string
     */
    /** @param ReflectionAttribute<Element|RootElement> $attribute */
    protected function getElementNamespace(ReflectionClass|ReflectionProperty $element, ReflectionAttribute $attribute): ?string
    {
        $namespace = $attribute->newInstance()->namespace;

        if ($namespace instanceof BackedEnum) {
            return (string) $namespace->value;
        }

        return $namespace;
    }

    /**
     * Ermittelt den Namespace eines Kindelements.
     *
     * Sofern usePropertyNamespaces aktiv ist, gewinnt die im Element-Attribut der Eigenschaft
     * deklarierte Namespace. Andernfalls wird der targetNamespace des Schema-Attributs der
     * Klasse verwendet (Standard-Verhalten des CII Modells).
     *
     * @param ReflectionProperty $property
     * @param ReflectionAttribute<Element> $elementAttribute
     * @param ReflectionAttribute<Schema>|false $schema
     * @return null|string
     */
    protected function resolveElementNamespace(
        ReflectionProperty $property,
        ReflectionAttribute $elementAttribute,
        ReflectionAttribute|false $schema
    ): ?string {
        $schemaNamespace = $schema !== false
            ? $schema->newInstance()->targetNamespace
            : null;
        $propertyNamespace = $this->getElementNamespace($property, $elementAttribute);

        if ($this->usePropertyNamespaces === true) {
            return $propertyNamespace ?? $schemaNamespace;
        }

        return $schemaNamespace ?? $propertyNamespace;
    }

    /**
     * Liefert den voll qualifizierten Namen des XML Knotens
     * 
     * @param string $elementName
     * @param string $elementNamespace
     * @return string
     */
    protected function getElementQualifiedName(string $elementName, string $elementNamespace): string
    {
        $cases = $this->getNamespaceMapping();
        return strtolower($cases[$elementNamespace]) . ':' . $elementName;
    }

    /**
     * Liefert den Wert eines Simple Content Elements
     * 
     * @param ReflectionProperty $element
     * @return null|string
     */
    protected function getElementValue(ReflectionProperty $element, object $object): ?string
    {
        $result = null;
        $value = $element->getValue($object);

        if (is_object($value) && $value instanceof ValueableInterface) {
            $result = $value->getValue();

            if ($result instanceof DateTimeInterface) {
                $rootClassName = $this->getRootClass($value::class);
                
                $reflector = new ReflectionClass($rootClassName);
                $extension = $reflector->getAttributes(Extension::class)[0] ?? false;

                if ($extension !== false) {
                    $instance = $extension->newInstance();
                    $base = $instance->getBase();

                    switch ($base) {
                        case 'xsd:date':
                        default:
                            $result = $result->format('Y-m-d');
                            break;
                    }
                }
            }
        } elseif (is_scalar($value)) {
            $result = $value;
        }

        if (is_bool($result)) {
            return $result ? 'true' : 'false';
        }

        return strval($result);
    }

    /**
     * Liefert das Namespace Mapping Array
     * Sofern die benutzten Namespaces noch nicht initialisiert sind, werden diese hier in ein Array geparst, 
     * dessen Keys die Namespace URI und dessen Werte die zu verwendenden Prefixes darstellen.
     * 
     * @return array
     */
    public function getNamespaceMapping(): array
    {
        if (! is_string($this->namespaces)) {
            return [];
        }

        if (empty($this->namespaceMapping) === true) {
            $this->namespaceMapping = array_map(
                fn(string $value): string => ($pos = strrpos($value, '_')) !== false
                    ? substr($value, $pos + 1)
                    : $value, 
                array_column($this->namespaces::cases(), 'name', 'value')
            );
        }

        return $this->namespaceMapping;
    }

    /**
     * Liefert den Namen der Elternklasse bei vererbten Klassen
     * Es wird der komplette Vererbungsbaum durchlaufen, bis die Root Klasse gefunden wurde.
     *
     * @param string $className
     * @return string
     */
    protected function getRootClass(string $className): string
    {
        $root = $className;
        $reflector = new ReflectionClass($className);
        while ($reflector = $reflector->getParentClass()) {
            $root = $reflector->getName();
        }

        return $root;
    }

    /**
     * Liefert die Target Namespace Eigenschaft des übergebenen Elements
     * Besitzt das übergenene Element kein Schema Attribut, wird false als Ergebnis geliefert.
     *
     * @param object $element
     * @return ReflectionAttribute<Schema>|false
     */
    protected function getSchema(object $element): ReflectionAttribute|false
    {
        $reflector = new ReflectionClass($element);
        $schema = $reflector->getAttributes(Schema::class)[0] ?? false;

        return $schema;
    }

    /**
     * Versucht zu ermitteln, ob die übergebene Klasse ein XSD ComplexType Element darstellt.
     * 
     * @param string $className
     * @return bool
     */
    protected function isComplexType(string $className): bool
    {
        $result = false;
        $isSimpleContent = $this->isSimpleContent($className);

        if ($isSimpleContent === false) {
            $reflector = new ReflectionClass($className);
            $complexType = $reflector->getAttributes(ComplexType::class)[0] ?? false;

            if ($complexType !== false) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Versucht zu ermitteln, ob das zu erzeugende Element ein SimpleContent Attribute besitzt.
     * Dabei wird der komplette Vererbungsbaum bis zur Root Klasse überprüft. Wird das Attribute
     * vorher gefunden, wird die Iteration abgebrochen.
     * 
     * @param string $className
     * @return bool
     */
    protected function isSimpleContent(string $className): bool
    {
        $isSimpleContent = false;
        $reflector = new ReflectionClass($className);

        $simpleContent = $reflector->getAttributes(SimpleContent::class)[0] ?? false;
        if ($simpleContent !== false) {
            $isSimpleContent = true;
        }

        while ($reflector = $reflector->getParentClass()) {
            $simpleContent = $reflector->getAttributes(SimpleContent::class)[0] ?? false;
            if ($simpleContent !== false) {
                $isSimpleContent = true;
                break;
            }
        }

        return $isSimpleContent;
    }

    /**
     * Serialisiert ein Objekt rekursiv in ein XML Element. Dabei werden die Eigenschaften des Objekts durchlaufen 
     * und die entsprechenden XML Knoten erstellt. Komplexe Typen werden als Kindknoten serialisiert, während einfache 
     * Typen als Textknoten serialisiert werden. Collections von komplexen Typen werden ebenfalls als Kindknoten serialisiert.
     * 
     * @param object $dto Das zu serialisierende Objekt
     * @param DOMElement $parent Das übergeordnete XML Element, an das die serialisierten Knoten angehängt werden
     * @param DOMDocument $dom Das DOMDocument, das für die Erstellung der XML Knoten verwendet wird
     * @return void
     */
    protected function serializeObject(object $dto, DOMElement $parent, DOMDocument $dom): void
    {
        $reflector = new ReflectionClass($dto);
        $schema = $reflector->getAttributes(Schema::class)[0] ?? false;

        $sequence = $this->generateSequenceHeap($reflector->getProperties());

        foreach ($sequence as $property) {

            // Wert der Eigenschaft ermitteln.
            $value = $property->getValue($dto);
            if ($value === null || ($value instanceof SplObjectStorage && $value->count() === 0)) {
                continue;
            }
        
            // Element Attribut ermitteln. Ist keines vorhanden, wird die 
            $elementAttribute = $property->getAttributes(Element::class)[0] ?? false;
            if ($elementAttribute === false) {
                continue;
            }

            // Name, Namespace und Wert des Elements ermitteln
            $nodeName = $this->getElementName($property, $elementAttribute);
            $nodeNamespace = $this->resolveElementNamespace($property, $elementAttribute, $schema);
            $nodeValue = $this->getElementValue($property, $dto);

            $qualifiedName = $this->getElementQualifiedName($nodeName, $nodeNamespace);

            // rekursives serialisieren von komplexen Typen als Kindknoten
            if (is_object($value) && $this->isComplexType($value::class)) {
                $child = $dom->createElementNs($nodeNamespace, $qualifiedName);
                $parent->appendChild($child);
                $this->serializeObject($value, $child, $dom);
            }

            // collections ein und des selben komplexen Typen als Kindknoten serialisieren
            elseif ($value instanceof SplObjectStorage && $elementAttribute->newInstance()->maxOccurs > 1) {
                foreach ($value as $instance) {
                    if ($this->isComplexType($instance::class)) {
                        $child = $dom->createElementNs($nodeNamespace, $qualifiedName);
                        $parent->appendChild($child);
                        $this->serializeObject($instance, $child, $dom);
                    } else {
                        $instanceValue = '';
                        if ($instance instanceof ValueableInterface) {
                            $instanceValue = $instance->getValue();
                        }

                        $child = $dom->createElementNs(
                            $nodeNamespace, 
                            $qualifiedName,
                            strval($instanceValue),
                        );

                        $child = $this->setElementAttributes($child, $instance);
                        $parent->appendChild($child);
                    }
                }
            }

            // Simple Type als Textknoten serialisieren
            else {
                $child = $dom->createElementNs($nodeNamespace, $qualifiedName, strval($nodeValue));
                $child = $this->setElementAttributes($child, $value);
                $parent->appendChild($child);
            }
        }
    }

    /**
     * Setzt die Attribute eines XML Elements basierend auf den Attributen der übergebenen Klasse. Es werden 
     * alle Eigenschaften der Klasse durchlaufen, die das Attribut Attribute besitzen. Für jedes gefundene Attribut 
     * wird der Wert ermittelt und als Attribut am XML Element gesetzt. Es werden nur Attribute gesetzt, deren Wert 
     * nicht null ist.
     * 
     * @param DOMElement $element Das XML Element, an das die Attribute angehängt werden
     * @param mixed $reference Das Objekt, dessen Attribute ausgelesen und am XML Element gesetzt werden
     * @return DOMElement
     */
    protected function setElementAttributes(DOMElement $element, mixed $reference): DOMElement
    {
        if (!is_object($reference)) {
            return $element;
        }

        $elementAttributes = $this->getElementAttributes($reference::class);
        foreach ($elementAttributes as $attribute) {
            $attributeAttribute = $attribute->getAttributes(Attribute::class)[0]->newInstance();
            $attributeValue = $attribute->getValue($reference);

            if ($attributeValue !== null) {
                $element->setAttribute($attributeAttribute->name ?? $attribute->getName(), $attributeValue);
            }
        }

        return $element;
    }

    /**
     * Setzt den Fully Qualified Class Name (FQCN) der anzuwendenden Namespace Enumeration
     * 
     * @param string $namespaces
     * @return void
     */
    public function setNamespaces(string $namespaces): void
    {
        $this->namespaces = $namespaces;
    }

    /**
     * Steuert, ob für Kindelemente die im Element-Attribut deklarierten Namespaces den Vorrang
     * vor der Schema targetNamespace der Klasse haben.
     *
     * @param bool $usePropertyNamespaces
     * @return void
     */
    public function setUsePropertyNamespaces(bool $usePropertyNamespaces): void
    {
        $this->usePropertyNamespaces = $usePropertyNamespaces;
    }
}