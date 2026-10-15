<?php

namespace App\Console\Commands;

use App\Helpers\TimeHelper;
use App\Models\Dispensasi;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCompleteDispensasi extends Command
{
    protected $signature = 'dispensasi:auto-complete';
    protected $description = 'Otomatis menyelesaikan dispensasi menggantung setelah jam KBM terakhir pada hari tersebut';

    public function __construct(
        protected ?NotifikasiService $notifikasiService = null
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = \App\Services\DispensasiService::autoExpireOverdueDispensasi();

        $this->info("Berhasil auto-selesaikan {$count} dispensasi setelah jam KBM terakhir.");

        return 0;
    }
}
