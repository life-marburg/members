<?php

namespace App\Console\Commands;

use App\Services\SheetService;
use Illuminate\Console\Command;

class RefreshSheetsCache extends Command
{
    protected $signature = 'sheets:refresh';

    protected $description = 'Refresh the sheets from webdav';

    protected SheetService $sheetService;

    public function __construct(SheetService $sheetService)
    {
        parent::__construct();
        $this->sheetService = $sheetService;
    }

    public function handle()
    {
        $this->line('Refreshing sheets cache. This may take a while...');
        $this->sheetService->refreshSheetsCache();
        $this->info('Done!');

        return Command::SUCCESS;
    }
}
