<?php

declare(strict_types=1);

namespace MMNewmedia\Xsd;

use Attribute;
use BackedEnum;

/**
 * Deklariert einen XSD SimpleType mit Enumeration Restriction:
 *
 * ```xml
 * <xs:simpleType name="CurrencyCodeType">
 *   <xs:restriction base="xs:string">
 *     <xs:enumeration value="EUR"/>
 *     <xs:enumeration value="USD"/>
 *   </xs:restriction>
 * </xs:simpleType>
 * ```
 *
 * Die zulässigen Werte werden ausschließlich aus der übergebenen BackedEnum abgeleitet,
 * um eine einzige Quelle der Wahrheit in PHP zu behalten.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Enumeration
{
    /**
     * @var list<string> Die aus der Enumeration abgeleiteten Zulässigkeitswerte
     */
    public array $values;

    /**
     * @param class-string<BackedEnum> $enum Die Enumeration, aus der die Werte abgeleitet werden
     * @param null|string $name Der Name des deklarierten SimpleTypes, z.B. "CurrencyCodeType"
     * @param string $base Der Basistyp der Restriction, z.B. "xsd:string"
     * @param bool $validate Ob die Werte zur Laufzeit beim Serialisieren geprüft werden
     */
    public function __construct(
        public string $enum,
        public ?string $name = null,
        public string $base = 'xsd:string',
        public bool $validate = true,
    ) {
        $this->values = array_map(
            static fn(BackedEnum $case): string => strval($case->value),
            $enum::cases(),
        );
    }
}
