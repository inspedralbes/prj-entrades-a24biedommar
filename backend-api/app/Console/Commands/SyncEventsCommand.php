<?php

namespace App\Console\Commands;

use App\Models\Esdeveniment;
use App\Services\TicketmasterService;
use Illuminate\Console\Command;

class SyncEventsCommand extends Command
{
    protected $signature = 'events:sync
                            {--force : Forçar sincronització independentment del cache}
                            {--limit= : Limitar nombre d\'esdeveniments}
                            {--event-id= : Sincronitzar un sol event específic}';

    protected $description = 'Sincronitza esdeveniments des de Ticketmaster';

    public function __construct(
        private TicketmasterService $ticketmasterService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = $this->option('force');
        $limit = $this->option('limit');
        $eventId = $this->option('event-id');

        $this->info('Iniciant sincronització d\'esdeveniments...');

        if ($eventId) {
            return $this->syncSingleEvent($eventId);
        }

        return $this->syncAllEvents($limit, $force);
    }

    private function syncSingleEvent(string $eventId): int
    {
        $this->info("Sincronitzant event: {$eventId}");

        $eventData = $this->ticketmasterService->getEventById($eventId);

        if (! $eventData) {
            $this->error("No s'ha pogut obtenir l'event de Ticketmaster");
            return Command::FAILURE;
        }

        $this->saveEvent($eventData);

        $this->info("Event sincronitzat correctament");
        return Command::SUCCESS;
    }

    private function syncAllEvents(?int $limit, bool $force): int
    {
        $mida = 200;
        if ($limit !== null) {
            $mida = $limit;
        }
        $eventsData = $this->ticketmasterService->getAllEvents($mida);

        if (empty($eventsData)) {
            $this->error('No s\'han pogut obtenir esdeveniments de Ticketmaster');
            return Command::FAILURE;
        }

        $count = 0;
        $errors = [];

        foreach ($eventsData as $eventData) {
            try {
                $this->saveEvent($eventData);
                $count++;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        $this->info("Sincronitzats {$count} esdeveniments");

        if (! empty($errors)) {
            $this->warn('Errors durant la sincronització:');
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }

        return Command::SUCCESS;
    }

    private function saveEvent(array $eventData): Esdeveniment
    {
        $existing = Esdeveniment::where('tm_id', $eventData['id'])->first();

        $ubicacio = [];
        if (isset($eventData['ubicacio']) && is_array($eventData['ubicacio'])) {
            $ubicacio = $eventData['ubicacio'];
        }

        $nomRecinte = null;
        if (array_key_exists('recinte', $ubicacio)) {
            $nomRecinte = $ubicacio['recinte'];
        }
        $tmCiutat = null;
        if (array_key_exists('ciutat', $ubicacio)) {
            $tmCiutat = $ubicacio['ciutat'];
        }
        $latitud = null;
        if (array_key_exists('latitud', $ubicacio)) {
            $latitud = $ubicacio['latitud'];
        }
        $longitud = null;
        if (array_key_exists('longitud', $ubicacio)) {
            $longitud = $ubicacio['longitud'];
        }

        $dataEsdeveniment = null;
        if (array_key_exists('data', $eventData)) {
            $dataEsdeveniment = $eventData['data'];
        }
        $tmHora = null;
        if (array_key_exists('hora', $eventData)) {
            $tmHora = $eventData['hora'];
        }
        $urlImatge = null;
        if (array_key_exists('imatge', $eventData)) {
            $urlImatge = $eventData['imatge'];
        }
        $descripcio = null;
        if (array_key_exists('descripcio', $eventData)) {
            $descripcio = $eventData['descripcio'];
        }
        $tmInformacio = null;
        if (array_key_exists('informacio', $eventData)) {
            $tmInformacio = $eventData['informacio'];
        }
        $tmUrlOficial = null;
        if (array_key_exists('url_oficial', $eventData)) {
            $tmUrlOficial = $eventData['url_oficial'];
        }
        $tmMapaSeients = null;
        if (array_key_exists('mapa_seients', $eventData)) {
            $tmMapaSeients = $eventData['mapa_seients'];
        }

        $imatges = [];
        if (isset($eventData['imatges']) && is_array($eventData['imatges'])) {
            $imatges = $eventData['imatges'];
        }
        $venue = [];
        if (isset($eventData['venue']) && is_array($eventData['venue'])) {
            $venue = $eventData['venue'];
        }
        $classificacions = [];
        if (isset($eventData['classificacions']) && is_array($eventData['classificacions'])) {
            $classificacions = $eventData['classificacions'];
        }
        $preusComplets = [];
        if (isset($eventData['preus_complets']) && is_array($eventData['preus_complets'])) {
            $preusComplets = $eventData['preus_complets'];
        }
        $artistes = [];
        if (isset($eventData['artistes']) && is_array($eventData['artistes'])) {
            $artistes = $eventData['artistes'];
        }

        $acc = null;
        if (array_key_exists('accessibilitat', $eventData)) {
            $acc = $eventData['accessibilitat'];
        }
        $aparc = null;
        if (array_key_exists('aparcament', $eventData)) {
            $aparc = $eventData['aparcament'];
        }
        $portes = null;
        if (array_key_exists('portes_info', $eventData)) {
            $portes = $eventData['portes_info'];
        }

        $data = [
            'tm_id' => $eventData['id'],
            'titol' => $eventData['nom'],
            'data_esdeveniment' => $dataEsdeveniment,
            'tm_hora' => $tmHora,
            'nom_recinte' => $nomRecinte,
            'tm_ciutat' => $tmCiutat,
            'url_imatge' => $urlImatge,
            'descripcio' => $descripcio,
            'tm_descripcio' => $descripcio,
            'tm_informacio' => $tmInformacio,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'actiu' => true,
            'tm_imatges' => json_encode($imatges),
            'tm_venue' => json_encode($venue),
            'tm_classificacions' => json_encode($classificacions),
            'tm_preus' => json_encode($preusComplets),
            'tm_artistes' => json_encode($artistes),
            'tm_url_oficial' => $tmUrlOficial,
            'tm_mapa_seients' => $tmMapaSeients,
            'tm_accessibilitat' => json_encode($acc),
            'tm_aparcament' => json_encode($aparc),
            'tm_portes_info' => json_encode($portes),
        ];

        if ($existing) {
            $existing->update($data);
            return $existing;
        }

        return Esdeveniment::create($data);
    }
}