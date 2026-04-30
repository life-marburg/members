<?php

namespace Tests\Unit\Models;

use App\Models\SharedFolder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_public_is_cast_to_boolean(): void
    {
        $folder = SharedFolder::create([
            'path' => 'docs',
            'group_id' => null,
            'is_public' => 1,
        ]);

        $this->assertSame(true, $folder->fresh()->is_public);
    }

    public function test_is_public_defaults_to_false(): void
    {
        $folder = SharedFolder::create([
            'path' => 'docs',
            'group_id' => null,
        ]);

        $this->assertSame(false, $folder->fresh()->is_public);
    }
}
