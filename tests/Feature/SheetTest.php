<?php

namespace Tests\Feature;

use App\Models\Instrument;
use App\Models\InstrumentGroup;
use App\Services\SheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class SheetTest extends TestCase
{
    use RefreshDatabase;

    private SheetService $sheetService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partialMock(SheetService::class, function (MockInterface $mock) {
            $mock
                ->shouldAllowMockingProtectedMethods()
                ->shouldReceive('getSongFileStructure')
                ->andReturn([
                    'Song1' => [
                        'Song1.TenorSax.1.pdf',
                        'Song1.Timpani.1.pdf',
                        'Song1.Trompete.1.pdf',
                        'Song1.Trompete.2.pdf',
                        'Song1.Tuba.1.pdf',
                    ],
                    'Song2' => [
                        'Song1.AltSax.1.pdf',
                        'Song1.AltSax.2.pdf',
                        'Song1.Timpani.1.pdf',
                        'Song1.Trompete.1.pdf',
                        'Song1.Trompete.2.pdf',
                        'Song1.Tuba.1.pdf',
                    ],
                ]);
        });
        $this->sheetService = resolve(SheetService::class);
    }

    public function test_get_sheets()
    {
        $trumpet = Instrument::whereId(9)->first();
        $sheets = $this->sheetService->getSheetsForInstrument($trumpet)->toArray();

        $this->assertEquals([
            ['title' => '1. Stimme', 'path' => '1'],
            ['title' => '2. Stimme', 'path' => '2'],
        ], $sheets['Song1']);
        $this->assertEquals([
            ['title' => '1. Stimme', 'path' => '1'],
            ['title' => '2. Stimme', 'path' => '2'],
        ], $sheets['Song2']);
    }

    public function test_get_sheets_invalid_instrument()
    {
        $sheets = $this->sheetService->getSheetsForInstrument(null);

        $this->assertNull($sheets);
    }

    // TODO: valid group, invalid instrument
    // TODO: (maybe not in this test class) users should only be able to access instrument pages of their instrument group
}
