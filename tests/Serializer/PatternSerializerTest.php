<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use MMNewmedia\Serializer\JsonDtoSerializer;
use MMNewmedia\Serializer\XmlDtoSerializer;
use MMNewmedia\Tests\Fixtures\MultiPatternType;
use MMNewmedia\Tests\Fixtures\Namespaces;
use MMNewmedia\Tests\Fixtures\PatternType;
use MMNewmedia\Tests\Fixtures\TestPatternType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PatternSerializerTest extends TestCase
{
    private XmlDtoSerializer $xmlSerializer;
    private JsonDtoSerializer $jsonSerializer;

    #[\Override]
    protected function setUp(): void
    {
        $this->xmlSerializer = new XmlDtoSerializer();
        $this->xmlSerializer->setNamespaces(Namespaces::class);

        $this->jsonSerializer = new JsonDtoSerializer();
        $this->jsonSerializer->setRootClasses(['TestPattern' => TestPatternType::class]);
    }

    public function testXmlSerializeAcceptsValueMatchingPattern(): void
    {
        $xml = $this->xmlSerializer->serialize(self::dto('ABC', 'XYZ'));

        self::assertStringContainsString('<inv:Pattern>ABC</inv:Pattern>', $xml);
        self::assertStringContainsString('<inv:MultiPattern>XYZ</inv:MultiPattern>', $xml);
    }

    public function testXmlSerializeRejectsValueNotMatchingPattern(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'abc' does not match pattern '^[A-Z]{3}$'.");

        $this->xmlSerializer->serialize(self::dto('abc', 'XYZ'));
    }

    public function testXmlSerializeRejectsValueNotMatchingSecondPattern(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'AEZ' does not match pattern '^[^AEIOU]+$'.");

        $this->xmlSerializer->serialize(self::dto('ABC', 'AEZ'));
    }

    public function testJsonSerializeAcceptsValueMatchingPattern(): void
    {
        $data = json_decode($this->jsonSerializer->serialize(self::dto('ABC', 'XYZ')), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(
            [
                'TestPattern' => [
                    'Pattern' => 'ABC',
                    'MultiPattern' => 'XYZ',
                ],
            ],
            $data
        );
    }

    public function testJsonSerializeRejectsValueNotMatchingPattern(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'abc' does not match pattern '^[A-Z]{3}$'.");

        $this->jsonSerializer->serialize(self::dto('abc', 'XYZ'));
    }

    public function testJsonUnserializeRejectsValueNotMatchingPattern(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Value 'abc' does not match pattern '^[A-Z]{3}$'.");

        $this->jsonSerializer->unserialize(
            '{"TestPattern":{"Pattern":"abc","MultiPattern":"XYZ"}}'
        );
    }

    public function testJsonUnserializeAcceptsValueMatchingPattern(): void
    {
        $dto = $this->jsonSerializer->unserialize(
            '{"TestPattern":{"Pattern":"ABC","MultiPattern":"XYZ"}}'
        );

        self::assertInstanceOf(TestPatternType::class, $dto);
        self::assertSame('ABC', $dto->pattern->getValue());
        self::assertSame('XYZ', $dto->multiPattern->getValue());
    }

    private static function dto(string $pattern, string $multiPattern): TestPatternType
    {
        $patternType = new PatternType();
        $patternType->setValue($pattern);

        $multiPatternType = new MultiPatternType();
        $multiPatternType->setValue($multiPattern);

        return new TestPatternType($patternType, $multiPatternType);
    }
}
