<?php

namespace Tests\Feature\Models;

use App\Models\Instrument;
use App\Models\Sheet;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_sheet_belongs_to_song(): void
    {
        $sheet = Sheet::factory()->create();

        $this->assertInstanceOf(Song::class, $sheet->song);
    }

    public function test_sheet_belongs_to_instrument(): void
    {
        $sheet = Sheet::factory()->create();

        $this->assertInstanceOf(Instrument::class, $sheet->instrument);
    }

    public function test_sheet_has_display_title(): void
    {
        $sheet = Sheet::factory()->create([
            'part_number' => 1,
            'variant' => null,
        ]);

        $this->assertEquals('1. Stimme', $sheet->display_title);
    }

    public function test_sheet_has_display_title_with_variant(): void
    {
        $sheet = Sheet::factory()->create([
            'part_number' => 2,
            'variant' => 'Solo',
        ]);

        $this->assertEquals('2. Stimme Solo', $sheet->display_title);
    }

    public function test_sheet_has_display_title_non_numeric_part(): void
    {
        $sheet = Sheet::factory()->create([
            'part_number' => null,
            'variant' => 'Direktion',
        ]);

        $this->assertEquals('Direktion', $sheet->display_title);
    }
}
