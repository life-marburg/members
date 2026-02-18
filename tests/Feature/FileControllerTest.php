<?php

namespace Tests\Feature;

use App\Livewire\FileBrowser;
use App\Models\Group;
use App\Models\SharedFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FileControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('shared');

        $this->user = User::factory()->create([
            'status' => User::STATUS_UNLOCKED,
        ]);

        $this->user->instrumentGroups()->attach(1);
        $this->user->personalData()->create([
            'street' => 'Test St 1',
            'city' => 'Test City',
            'zip' => '12345',
            'mobile_phone' => '0123456789',
        ]);

        $this->group = Group::factory()->create();
        $this->user->groups()->attach($this->group);
    }

    public function test_index_shows_only_shared_folders(): void
    {
        Storage::disk('shared')->put('docs/readme.txt', 'hello');
        Storage::disk('shared')->put('secret/private.txt', 'secret');

        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);

        $response = $this->actingAs($this->user)->get(route('files.index'));

        $response->assertOk();
        $response->assertSee('docs');
        $response->assertDontSee('secret');
    }

    public function test_unshared_folder_not_visible(): void
    {
        Storage::disk('shared')->put('docs/readme.txt', 'hello');

        $response = $this->actingAs($this->user)->get(route('files.index'));

        $response->assertOk();
        $response->assertDontSee('docs');
    }

    public function test_browse_shared_subfolder(): void
    {
        Storage::disk('shared')->put('docs/guide.pdf', 'pdf content');

        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);

        Livewire::actingAs($this->user)
            ->test(FileBrowser::class)
            ->call('navigateTo', 'docs')
            ->assertSee('guide.pdf');
    }

    public function test_blocked_subfolder_hidden(): void
    {
        Storage::disk('shared')->put('docs/public/file.txt', 'public');
        Storage::disk('shared')->put('docs/private/secret.txt', 'secret');

        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);
        SharedFolder::create(['path' => 'docs/private', 'group_id' => null]);

        Livewire::actingAs($this->user)
            ->test(FileBrowser::class)
            ->call('navigateTo', 'docs')
            ->assertSee('public')
            ->assertDontSee('private');
    }

    public function test_download_requires_share_access(): void
    {
        Storage::disk('shared')->put('docs/readme.txt', 'hello world');

        // No share record -- should be blocked
        Livewire::actingAs($this->user)
            ->test(FileBrowser::class)
            ->call('download', 'docs/readme.txt')
            ->assertForbidden();
    }

    public function test_download_with_share_access(): void
    {
        Storage::disk('shared')->put('docs/readme.txt', 'hello world');

        SharedFolder::create(['path' => 'docs', 'group_id' => $this->group->id]);

        Livewire::actingAs($this->user)
            ->test(FileBrowser::class)
            ->call('download', 'docs/readme.txt')
            ->assertFileDownloaded('readme.txt');
    }

    public function test_path_traversal_blocked(): void
    {
        Storage::disk('shared')->put('secret.txt', 'secret');

        Livewire::actingAs($this->user)
            ->test(FileBrowser::class)
            ->call('download', '../secret.txt')
            ->assertNotFound();
    }

    public function test_download_nonexistent_file_returns_404(): void
    {
        Livewire::actingAs($this->user)
            ->test(FileBrowser::class)
            ->call('download', 'nope.txt')
            ->assertNotFound();
    }

    public function test_unauthenticated_user_redirected(): void
    {
        $response = $this->get(route('files.index'));

        $response->assertRedirect();
    }

    public function test_navigate_to_unshared_folder_is_forbidden(): void
    {
        Storage::disk('shared')->put('secret/file.txt', 'data');

        Livewire::actingAs($this->user)
            ->test(FileBrowser::class)
            ->call('navigateTo', 'secret')
            ->assertForbidden();
    }
}
