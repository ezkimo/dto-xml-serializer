<?php

declare(strict_types=1);

namespace MMNewmedia\Tests\Serializer;

use MMNewmedia\Serializer\ChoiceValidator;
use MMNewmedia\Xsd\Choice;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplObjectStorage;

final class ChoiceValidatorTest extends TestCase
{
    public function testUnnamedChoicesFormOneGroupPerClass(): void
    {
        $dto = new class {
            #[Choice]
            public ?string $first = 'a';

            #[Choice]
            public ?string $second = null;
        };

        $this->validate($dto);

        $dto->second = 'b';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires exactly one value, got 2.');

        $this->validate($dto);
    }

    public function testEmptyCollectionCountsAsMissing(): void
    {
        $dto = new class {
            #[Choice(group: 'items')]
            public SplObjectStorage $items;

            #[Choice(group: 'items')]
            public ?string $fallback = null;

            public function __construct()
            {
                $this->items = new SplObjectStorage();
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Choice group "items" requires exactly one value, got 0.');

        $this->validate($dto);
    }

    public function testMinOccursZeroAllowsNoChoice(): void
    {
        $dto = new class {
            #[Choice(group: 'items', minOccurs: 0)]
            public ?string $first = null;

            #[Choice(group: 'items', minOccurs: 0)]
            public ?string $second = null;
        };

        $this->validate($dto);

        self::assertNull($dto->first);
    }

    public function testInconsistentOccursAreRejected(): void
    {
        $dto = new class {
            #[Choice(group: 'items', minOccurs: 0)]
            public ?string $first = null;

            #[Choice(group: 'items', minOccurs: 1)]
            public ?string $second = null;
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Inconsistent minOccurs/maxOccurs for choice group "items".');

        $this->validate($dto);
    }

    private function validate(object $dto): void
    {
        $validator = new class {
            use ChoiceValidator;

            public function validate(object $dto): void
            {
                $this->assertChoiceGroups($dto);
            }
        };

        $validator->validate($dto);
    }
}
