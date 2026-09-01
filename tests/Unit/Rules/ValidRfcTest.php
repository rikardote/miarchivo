<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidRfc;
use App\Services\RfcHelper;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidRfcTest extends TestCase
{
    public function test_it_accepts_valid_10_digit_rfcs()
    {
        $validRfcs = [
            'GOMA850215', // 15 Feb 1985
            'PEPJ900101', // 01 Jan 1990
            'LOGJ001231', // 31 Dec 2000
            'ROMA960229', // 29 Feb 1996 (leap year)
        ];

        foreach ($validRfcs as $rfc) {
            $validator = Validator::make(['rfc' => $rfc], ['rfc' => [new ValidRfc()]]);
            $this->assertTrue($validator->passes(), "Expected {$rfc} to be valid");
        }
    }

    public function test_it_rejects_invalid_rfcs()
    {
        $invalidRfcs = [
            'GOMA850215ABC', // 13 chars instead of 10
            'GOM850215',    // 9 chars
            '1234850215',   // Starts with numbers
            'GOMA850230',   // 30 Feb (invalid calendar day)
            'GOMA851301',   // Month 13 (invalid month)
            'GOMA850001',   // Month 00
            'GOMA850431',   // 31 April (April only has 30 days)
        ];

        foreach ($invalidRfcs as $rfc) {
            $validator = Validator::make(['rfc' => $rfc], ['rfc' => [new ValidRfc()]]);
            $this->assertFalse($validator->passes(), "Expected {$rfc} to be invalid");
        }
    }

    public function test_it_calculates_rfc_correctly()
    {
        $rfc = RfcHelper::calculate10('Alejandro', 'Gomez', 'Martinez', '1985-02-15');
        $this->assertEquals('GOMA850215', $rfc);

        // Jose Maria filter
        $rfc2 = RfcHelper::calculate10('Jose Antonio', 'Perez', 'Lopez', '1990-07-20');
        $this->assertEquals('PELA900720', $rfc2);

        // Inconvenient word replacement (e.g. PEDO -> PEDX)
        $rfc3 = RfcHelper::calculate10('Oscar', 'Perez', 'Dominguez', '1988-11-10');
        $this->assertEquals('PEDX881110', $rfc3);
    }
}
