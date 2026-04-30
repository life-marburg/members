<?php

namespace App\Services;

use App\Models\SharedFolder;
use App\Models\User;
use Illuminate\Support\Collection;

class SharedFolderService
{
    /**
     * Check if a user can access a given path.
     *
     * Resolution: find the most specific shared_folders record for the path
     * (exact match or longest parent prefix). At that level a row with
     * group_id = null and is_public = false blocks access; a row with
     * is_public = true grants access to anyone; otherwise membership in any
     * matching group_id grants access.
     */
    public function canAccess(User $user, string $path): bool
    {
        $path = trim($path, '/');
        $shares = $this->findSharesForPath($path);

        if ($shares->isEmpty()) {
            return false;
        }

        // Block (group_id = null, is_public = false) wins over everything else at this level
        if ($shares->contains(fn ($s) => $s->group_id === null && ! $s->is_public)) {
            return false;
        }

        if ($shares->contains(fn ($s) => $s->is_public)) {
            return true;
        }

        $userGroupIds = $user->groups()->pluck('groups.id');

        return $shares->pluck('group_id')->intersect($userGroupIds)->isNotEmpty();
    }

    /**
     * Get all root-level paths the user can access.
     * Returns a collection of path strings.
     */
    public function getAccessibleRootPaths(User $user): Collection
    {
        $userGroupIds = $user->groups()->pluck('groups.id');

        return SharedFolder::query()
            ->where(fn ($q) => $q->whereIn('group_id', $userGroupIds)->orWhere('is_public', true))
            ->pluck('path')
            ->map(fn ($path) => explode('/', $path)[0])
            ->unique()
            ->values();
    }

    /**
     * Check if a user can navigate to a given path.
     *
     * Unlike canAccess(), this also allows navigation to folders that
     * aren't directly shared but contain shared subfolders. This enables
     * users to reach deeply nested shared folders through their parents.
     */
    public function canNavigate(User $user, string $path): bool
    {
        if ($this->canAccess($user, $path)) {
            return true;
        }

        // Check if any shared subfolders exist under this path
        $path = trim($path, '/');
        $userGroupIds = $user->groups()->pluck('groups.id');

        return SharedFolder::where('path', 'like', $path.'/%')
            ->where(fn ($q) => $q->whereIn('group_id', $userGroupIds)->orWhere('is_public', true))
            ->exists();
    }

    /**
     * Given a list of child paths within a parent, filter out those
     * the user cannot access (due to subfolder overrides).
     * Files (non-folders) always pass -- only subfolder overrides can block.
     */
    public function filterAccessiblePaths(User $user, string $parentPath, array $childPaths): array
    {
        return array_values(array_filter($childPaths, function ($childPath) use ($user) {
            // If this child has its own share records, check those specifically
            $childShares = SharedFolder::where('path', trim($childPath, '/'))->get();

            if ($childShares->isEmpty()) {
                // No override -- inherits from parent (which is already checked)
                return true;
            }

            // Has override -- check if user has access via the override
            if ($childShares->contains(fn ($s) => $s->group_id === null && ! $s->is_public)) {
                return false;
            }

            if ($childShares->contains(fn ($s) => $s->is_public)) {
                return true;
            }

            $userGroupIds = $user->groups()->pluck('groups.id');

            return $childShares->pluck('group_id')->intersect($userGroupIds)->isNotEmpty();
        }));
    }

    /**
     * Find the most specific shared_folders records for a path.
     * Walks up from the exact path to root, returns all records at
     * the most specific level that has any records.
     */
    private function findSharesForPath(string $path): Collection
    {
        $segments = explode('/', $path);

        // Build candidate paths from most specific to least
        $candidates = [];
        for ($i = count($segments); $i >= 1; $i--) {
            $candidates[] = implode('/', array_slice($segments, 0, $i));
        }

        // Query once for all candidates
        $allShares = SharedFolder::whereIn('path', $candidates)->get();

        if ($allShares->isEmpty()) {
            return collect();
        }

        // Find the most specific (longest path) that has records
        foreach ($candidates as $candidate) {
            $shares = $allShares->where('path', $candidate);
            if ($shares->isNotEmpty()) {
                return $shares;
            }
        }

        return collect();
    }
}
