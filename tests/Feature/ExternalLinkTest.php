<?php

namespace Tests\Feature;

use App\Models\ExternalLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear seeded data from migration for isolated tests
        ExternalLink::query()->delete();
    }

    public function test_active_scope_returns_only_active_links(): void
    {
        ExternalLink::factory()->create(['is_active' => true]);
        ExternalLink::factory()->create(['is_active' => false]);

        $activeLinks = ExternalLink::active()->get();

        $this->assertCount(1, $activeLinks);
    }

    public function test_ordered_scope_returns_links_by_position(): void
    {
        ExternalLink::factory()->create(['position' => 2, 'title' => 'Second']);
        ExternalLink::factory()->create(['position' => 1, 'title' => 'First']);
        ExternalLink::factory()->create(['position' => 3, 'title' => 'Third']);

        $links = ExternalLink::ordered()->get();

        $this->assertEquals('First', $links[0]->title);
        $this->assertEquals('Second', $links[1]->title);
        $this->assertEquals('Third', $links[2]->title);
    }

    public function test_navigation_displays_active_external_links(): void
    {
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $user->personalData()->update(['street' => 'Test', 'city' => 'City', 'phone' => '123']);
        $user->instrumentGroups()->attach(1);

        ExternalLink::factory()->create([
            'title' => 'Test External Link',
            'url' => 'https://example.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('Test External Link');
        $response->assertSee('https://example.com');
    }

    public function test_inactive_links_not_displayed_in_navigation(): void
    {
        $user = User::factory()->create(['status' => User::STATUS_UNLOCKED]);
        $user->personalData()->update(['street' => 'Test', 'city' => 'City', 'phone' => '123']);
        $user->instrumentGroups()->attach(1);

        ExternalLink::factory()->create([
            'title' => 'Hidden Link',
            'url' => 'https://hidden.com',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertDontSee('Hidden Link');
    }
}
