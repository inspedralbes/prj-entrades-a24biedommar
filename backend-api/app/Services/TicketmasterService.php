<?php

//================================ NAMESPACES / IMPORTS ============

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

/**
 * Servei per gestionar la comunicació amb l'API de Ticketmaster.
 * A. Crida l'API externa de Ticketmaster.
 * B. Gestiona el cache a Redis.
 * C. Retorna les dades formatades.
 */
class TicketmasterService
{
    // A. URL base de l'API de Ticketmaster
    private const BASE_URL = 'https://app.ticketmaster.com/discovery/v2';

    // B. Clau de l'API des de configuració
    private string $apiKey;

    // C. Temps de cache en segons (15 minuts)
    private const CACHE_TTL = 900;

    /**
     * Constructor: Obté la clau de l'API des de variables d'entorn.
     */
    public function __construct()
    {
        $this->apiKey = config('services.ticketmaster.key', env('TICKETMASTER_API_KEY', ''));
    }

    /**
     * Obté el llistat d'esdeveniments.
     * A. Genera una clau única per al cache basada en els paràmetres.
     * B. Comprova si les dades estan al cache de Redis.
     * C. Si no estan al cache, crida l'API de Ticketmaster.
     * D. Desa les dades al cache i les retorna.
     */
    public function getEvents(?float $lat, ?float $lng, int $radius = 50): array
    {
        $cacheKey = $this->generateCacheKey('events', [
            'lat' => $lat,
            'lng' => $lng,
            'radius' => $radius,
        ]);

        $cached = Redis::get($cacheKey);
        if ($cached !== null) {
            return json_decode($cached, true);
        }

        $queryParams = [
            'apikey' => $this->apiKey,
            'size' => 20,
            'sort' => 'date,asc',
        ];

        if ($lat !== null && $lng !== null) {
            $queryParams['geoPoint'] = $this->calculateGeoPoint($lat, $lng);
            $queryParams['radius'] = $radius;
            $queryParams['unit'] = 'km';
        }

        try {
            $response = Http::get(self::BASE_URL . '/events.json', $queryParams);
            $data = $response->json();

            $events = $this->transformEvents($data);
            Redis::setex($cacheKey, self::CACHE_TTL, json_encode($events));

            return $events;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obté el detall d'un esdeveniment específic per ID.
     * A. Comprova el cache abans de fer la crida a l'API.
     * B. Crida l'API de Ticketmaster per obtenir el detall complet.
     * C. Transforma les dades i les retorna.
     */
    public function getEventById(string $id): ?array
    {
        $cacheKey = $this->generateCacheKey('event', ['id' => $id]);

        $cached = Redis::get($cacheKey);
        if ($cached !== null) {
            return json_decode($cached, true);
        }

        try {
            $response = Http::get(self::BASE_URL . '/events/' . $id . '.json', [
                'apikey' => $this->apiKey,
            ]);

            if ($response->status() !== 200) {
                return null;
            }

            $data = $response->json();
            $event = $this->transformEventDetail($data);

            Redis::setex($cacheKey, self::CACHE_TTL, json_encode($event));

            return $event;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Genera una clau única per al cache.
     * A. Rep el prefix i els paràmetres.
     * B. Crea un hash única per identificar la petició.
     */
    private function generateCacheKey(string $prefix, array $params): string
    {
        return 'ticketmaster:' . $prefix . ':' . md5(json_encode($params));
    }

    /**
     * Calcula el punt geogràfic per a la cerca de proximitat.
     * A. Converteix lat/lng a un format acceptat per Ticketmaster.
     * B. Retorna el punt geogràfic codificat.
     */
    private function calculateGeoPoint(float $lat, float $lng): string
    {
        return round($lat, 4) . ',' . round($lng, 4);
    }

    /**
     * Transforma la llista d'esdeveniments des del format de Ticketmaster.
     * A. Itera sobre els esdeveniments.
     * B. Extreu les dades rellevants (id, nom, data, imatge, ubicació, preu).
     * C. Retorna un array amb els esdeveniments transformats.
     */
    private function transformEvents(array $data): array
    {
        $events = [];
        $embedded = $data['_embedded'] ?? [];
        $eventItems = $embedded['events'] ?? [];

        foreach ($eventItems as $eventItem) {
            $events[] = $this->transformEventBasic($eventItem);
        }

        return $events;
    }

    /**
     * Transforma les dades bàsiques d'un esdeveniment.
     */
    private function transformEventBasic(array $event): array
    {
        $images = $event['images'] ?? [];
        $imageUrl = $this->getImageUrl($images, '16:9');

        $dates = $event['dates'] ?? [];
        $start = $dates['start'] ?? [];
        $eventDate = $start['localDate'] ?? '';
        $eventTime = $start['localTime'] ?? '';

        $classifications = $event['classifications'] ?? [];
        $category = $classifications[0]['segment']['name'] ?? 'Esdeveniment';

        $venues = $event['_embedded']['venues'] ?? [];
        $venue = $venues[0] ?? [];
        $venueName = $venue['name'] ?? '';
        $city = $venue['city']['name'] ?? '';

        $priceRanges = $event['priceRanges'] ?? [];
        $minPrice = $priceRanges[0]['min'] ?? null;
        $maxPrice = $priceRanges[0]['max'] ?? null;

        return [
            'id' => $event['id'],
            'nom' => $event['name'],
            'data' => $eventDate,
            'hora' => $eventTime,
            'imatge' => $imageUrl,
            'categoria' => $category,
            'ubicacio' => [
                'recinte' => $venueName,
                'ciutat' => $city,
            ],
            'preu' => [
                'min' => $minPrice,
                'max' => $maxPrice,
                'moneda' => $priceRanges[0]['currency'] ?? 'EUR',
            ],
            'url' => $event['url'] ?? '',
        ];
    }

    /**
     * Transforma el detall complet d'un esdeveniment.
     */
    private function transformEventDetail(array $event): array
    {
        $basic = $this->transformEventBasic($event);

        $basic['descripcio'] = $event['pleaseNote'] ?? '';
        $basic['informacio'] = $event['info'] ?? '';

        $images = $event['images'] ?? [];
        $basic['imatge_gran'] = $this->getImageUrl($images, '16:9');

        $seatmap = $event['seatmap'] ?? [];
        $basic['mapa_seients'] = $seatmap['staticUrl'] ?? '';

        return $basic;
    }

    /**
     * Obté la URL de la imatge segons la mida preferida.
     * A. Busca la imatge amb el format demanat.
     * B. Si no la troba, retorna la primera imatge disponible.
     */
    private function getImageUrl(array $images, string $ratio = '16:9'): string
    {
        foreach ($images as $image) {
            if ($image['ratio'] === $ratio) {
                return $image['url'];
            }
        }

        return $images[0]['url'] ?? '';
    }
}