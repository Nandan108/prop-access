<?php

namespace Nandan108\PropAccess\Tests;

use Nandan108\PropAccess\Support\CaseConverter;
use PHPUnit\Framework\TestCase;

final class CaseConverterTest extends TestCase
{
    public function testCaseIsCorrectlyConvertedComponents(): void
    {
        $inputs = ['postal_code', 'PostalCode', 'postalCode', 'postal-code', 'postal code', 'postal+code'];

        $outputs = [
            'pascal'      => 'PostalCode',
            'snake'       => 'postal_code',
            'kebab'       => 'postal-code',
            'camel'       => 'postalCode',
            'upper_snake' => 'POSTAL_CODE',
        ];

        foreach ($outputs as $case => $expected) {
            foreach ($inputs as $input) {
                $this->assertSame($expected, CaseConverter::to($case, $input));
                $this->assertSame($expected, CaseConverter::{'to'.CaseConverter::toPascal($case)}($input));
            }
        }
    }
}
