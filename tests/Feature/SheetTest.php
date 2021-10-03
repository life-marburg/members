<?php

namespace Tests\Feature;

use App\Services\SheetService;
use Mockery\MockInterface;
use Tests\TestCase;

class SheetTest extends TestCase
{
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
        $sheets = $this->sheetService->getSheetsForInstrument('trumpet')->toArray();

        $this->assertNotContains('Song1.TenorSax.1.pdf', $sheets['Song1']);
        $this->assertContains('Song1.Trompete.1.pdf', $sheets['Song1']);
        $this->assertContains('Song1.Trompete.2.pdf', $sheets['Song1']);
    }

    public function test_get_sheets_invalid_instrument()
    {
        $sheets = $this->sheetService->getSheetsForInstrument('not-an-instrument');

        $this->assertNull($sheets);
    }
}
