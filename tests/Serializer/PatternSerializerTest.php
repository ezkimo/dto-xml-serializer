<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use MMNewmedia\Serializer\JsonDtoSerializer;
use MMNewmedia\Serializer\XmlDtoSerializer;
use MMNewmedia\Tests\Fixtures\Namespaces;
use MMNewmedia\Tests\Fixtures\PatternType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PatternSerializerTest extends TestCase
{
    private XmlDtoSerializer $xmlSerializer;
    private JsonDtoSerializer $jsonSerializer;

    protected function setUp(): void
    {
        $this->xmlSerializer = new XmlDtoSerializer();
        $this->xmlSerializer->setNamespaces(\MMNewmedia\Tests\Fixtures\Namespaces::class);
        $this->jsonSerializer = new JsonDtoSerializer();
    }

    public function testXmlSerializeAcceptsValueMatchingPattern(): void
    {
        $patternType = new PatternType();
        $patternType->setValue('ABC');

        $xml = $this->xmlSerializer->serialize($patternType);

        self::assertStringContainsString('ABC', $xml);
    }

    public function testXmlSerializeRejectsValueNotMatchingPattern(): void
    {
        $this->expectException(RuntimeException::class);

        $patternType = new PatternType();
        $patternType->setValue('abc');

        $this->xmlSerializer->serialize($patternType);
    }

    public function testJsonSerializeRejectsValueNotMatchingPattern(): void
    {
        $this->expectException(RuntimeException::class);

        $patternType = new PatternType();
        $patternType->setValue('123');

        $this->jsonSerializer->serialize($patternType);
    }

    public function testJsonUnserializeRejectsValueNotMatchingPattern(): void
    {
        $this->expectException(RuntimeException::class);

        $this->jsonSerializer->setRootClasses(['PatternType' => PatternType::class]);
        $this->jsonSerializer->unserialize('{"PatternType":"abc"}');
    }
}
