<?php

namespace App\Console\Commands;

use App\Services\WabaTemplateSyncService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;

class SyncWabaTemplates extends Command
{
    protected $signature = 'waba:sync-templates';
    protected $description = 'Sincroniza las plantillas de WhatsApp desde Meta.';

    public function handle(WabaTemplateSyncService $service): int
    {
        try {
            $result = $service->sync();
        } catch (RequestException $exception) {
            $this->error('Error al sincronizar plantillas: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Plantillas sincronizadas: '.$result['updated']);

        return self::SUCCESS;
    }
}
