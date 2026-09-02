<div style="width: 302px; height: 189px; font-family: 'Inter', sans-serif; background: white; color: black; padding: 12px; box-sizing: border-box; overflow: hidden;">
    <table style="width: 100%; height: 100%; border-collapse: collapse; table-layout: fixed;">
        <!-- Header -->
        <tr>
            <td colspan="2" style="height: 35px; border-bottom: 1px solid #f3f4f6; vertical-align: top;">
                <div style="font-size: 11px; font-weight: 900; text-transform: uppercase; line-height: 1.1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                    {{ $expedient->employee->last_name }}, {{ $expedient->employee->first_name }}
                </div>
                <div style="font-size: 9px; font-weight: 700; color: #6b7280; letter-spacing: 0.05em; margin-top: 2px;">
                    {{ $expedient->employee->rfc }}
                </div>
            </td>
        </tr>

        <!-- Middle: Stats and QR -->
        <tr>
            <td style="vertical-align: top; padding-top: 8px;">
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 6px; font-weight: 900; color: #9ca3af; text-transform: uppercase; line-height: 1;">Sucursal / Sede</div>
                    <div style="font-size: 9px; font-weight: 700; color: #1f2937; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                        {{ $expedient->employee->branch->name ?? 'N/A' }}
                    </div>
                </div>

                <div style="display: flex; align-items: center;">
                    <div>
                        <div style="font-size: 6px; font-weight: 900; color: #9ca3af; text-transform: uppercase; line-height: 1;">Tomo</div>
                        <div style="font-size: 22px; font-weight: 900; color: #000; line-height: 1;">{{ $expedient->volume_number }}</div>
                    </div>
                </div>
            </td>
            <td style="width: 100px; vertical-align: middle; text-align: right; padding-top: 5px;">
                <div style="display: inline-block; padding: 2px; border: 1px solid #f3f4f6;">
                    {!! QrCode::errorCorrection('L')->size(70)->generate($expedient->qr_content) !!}
                </div>
                <div style="font-size: 7px; font-weight: 900; text-align: center; margin-top: 2px; width: 70px; float: right;">
                    {{ $expedient->expedient_code }}
                </div>
            </td>
        </tr>

        <!-- Footer: Barcode -->
        <tr>
            <td colspan="2" style="height: 45px; vertical-align: bottom; text-align: center; border-top: 1px solid #f3f4f6; padding-top: 5px;">
                @php
                    $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                    echo $generator->getBarcode($expedient->expedient_code, $generator::TYPE_CODE_128, 1.2, 22);
                @endphp
                <div style="font-size: 5px; font-weight: 700; color: #d1d5db; letter-spacing: 0.2em; margin-top: 3px;">
                    ARCHIVO DIGITAL v1.0
                </div>
            </td>
        </tr>
    </table>
</div>
