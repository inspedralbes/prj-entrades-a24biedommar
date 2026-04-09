<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class TicketmasterService
{
    private const BASE_URL = 'https://app.ticketmaster.com/discovery/v2';
    private string $apiKey;
    private const CACHE_TTL = 900;

    public function __construct()
    {
        $clau = config('services.ticketmaster.key');
        if ($clau === null || $clau === '') {
            $clau = env('TICKETMASTER_API_KEY', '');
        }
        $this->apiKey = is_string($clau) ? $clau : '';
    }

    public function getEvents(?float $lat, ?float $lng, int $radius = 50): array
    {
        $cacheKey = $this->generateCacheKey('events', [
            'lat' => $lat,
            'lng' => $lng,
            'radius' => $radius,
            'only_priced' => true,
        ]);

        try {
            $cached = Redis::get($cacheKey);
            if ($cached !== null) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {
        }

        if ($this->apiKey === '') {
            return [];
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
            $response = Http::timeout(25)->get(self::BASE_URL . '/events.json', $queryParams);
            $data = $response->json();
            if (! is_array($data)) {
                return [];
            }

            $embedded = [];
            if (isset($data['_embedded']) && is_array($data['_embedded'])) {
                $embedded = $data['_embedded'];
            }
            $events = [];
            if (isset($embedded['events']) && is_array($embedded['events'])) {
                $events = $embedded['events'];
            }

            $events = $this->filtrarEsdevenimentsAmbPreu($events);

            try {
                Redis::setex($cacheKey, self::CACHE_TTL, json_encode($events));
            } catch (\Throwable $e) {
            }

            return $events;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAllEvents(int $size = 200): array
    {
        $cacheKey = $this->generateCacheKey('all_events', [
            'size' => $size,
            'only_priced' => true,
        ]);

        try {
            $cached = Redis::get($cacheKey);
            if ($cached !== null) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {
        }

        if ($this->apiKey === '') {
            return [];
        }

        $queryParams = [
            'apikey' => $this->apiKey,
            'size' => min($size, 200),
            'sort' => 'date,asc',
        ];

        $allEvents = [];
        $page = 0;
        $maxPages = 10;

        try {
            while ($page < $maxPages) {
                $queryParams['page'] = $page;
                $response = Http::timeout(25)->get(self::BASE_URL . '/events.json', $queryParams);
                $data = $response->json();

                if (! is_array($data)) {
                    break;
                }

                $embedded = [];
                if (isset($data['_embedded']) && is_array($data['_embedded'])) {
                    $embedded = $data['_embedded'];
                }
                $events = [];
                if (isset($embedded['events']) && is_array($embedded['events'])) {
                    $events = $embedded['events'];
                }

                if (empty($events)) {
                    break;
                }

                $events = $this->filtrarEsdevenimentsAmbPreu($events);
                $allEvents = array_merge($allEvents, $events);
                $page++;

                $totalPages = 0;
                if (isset($data['page']) && is_array($data['page']) && isset($data['page']['totalPages'])) {
                    $totalPages = (int) $data['page']['totalPages'];
                }
                if ($totalPages <= $page) {
                    break;
                }
            }

            if (! empty($allEvents)) {
                try {
                    Redis::setex($cacheKey, self::CACHE_TTL, json_encode($allEvents));
                } catch (\Throwable $e) {
                }
            }

            return $allEvents;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function searchEvents(string $keyword, int $size = 20): array
    {
        if ($this->apiKey === '') {
            return [];
        }

        $cacheKey = $this->generateCacheKey('search', [
            'keyword' => $keyword,
            'size' => $size,
            'only_priced' => true,
        ]);

        try {
            $cached = Redis::get($cacheKey);
            if ($cached !== null) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {
        }

        try {
            $response = Http::timeout(25)->get(self::BASE_URL . '/events.json', [
                'apikey' => $this->apiKey,
                'keyword' => $keyword,
                'size' => $size,
                'sort' => 'date,asc',
            ]);

            $data = $response->json();
            if (! is_array($data)) {
                return [];
            }

            $embedded = [];
            if (isset($data['_embedded']) && is_array($data['_embedded'])) {
                $embedded = $data['_embedded'];
            }
            $events = [];
            if (isset($embedded['events']) && is_array($embedded['events'])) {
                $events = $embedded['events'];
            }

            $events = $this->filtrarEsdevenimentsAmbPreu($events);

            try {
                Redis::setex($cacheKey, self::CACHE_TTL, json_encode($events));
            } catch (\Throwable $e) {
            }

            return $events;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getEventById(string $id): ?array
    {
        if ($this->apiKey === '') {
            return null;
        }

        // v=4: mateix filtre de preu que el llistat (invalida cache antiga)
        $cacheKey = $this->generateCacheKey('event', ['id' => $id, 'v' => 4]);

        $cached = Redis::get($cacheKey);
        if ($cached !== null) {
            return json_decode($cached, true);
        }

        try {
            $response = Http::get(self::BASE_URL . '/events/' . $id . '.json', [
                'apikey' => $this->apiKey,
                'expand' => 'venue,attractions',
            ]);

            if ($response->status() !== 200) {
                $response = Http::get(self::BASE_URL . '/events/' . $id . '.json', [
                    'apikey' => $this->apiKey,
                ]);
            }

            if ($response->status() !== 200) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data)) {
                return null;
            }

            if (! $this->discoveryEventTePreu($data)) {
                return null;
            }

            $event = $this->transformEventFull($data);

            Redis::setex($cacheKey, self::CACHE_TTL, json_encode($event));

            return $event;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFullEventDetails(string $id): ?array
    {
        return $this->getEventById($id);
    }

    private function generateCacheKey(string $prefix, array $params): string
    {
        return 'ticketmaster:' . $prefix . ':' . md5(json_encode($params));
    }

    /**
     * Discovery API: l'esdeveniment inclou almenys un rang de preu amb min o max.
     *
     * @param  array<string, mixed>  $event
     */
    private function discoveryEventTePreu(array $event): bool
    {
        if (! isset($event['priceRanges']) || ! is_array($event['priceRanges'])) {
            return false;
        }
        foreach ($event['priceRanges'] as $range) {
            if (! is_array($range)) {
                continue;
            }
            if (isset($range['min']) && $range['min'] !== null && $range['min'] !== '') {
                return true;
            }
            if (isset($range['max']) && $range['max'] !== null && $range['max'] !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $events
     * @return list<array<string, mixed>>
     */
    private function filtrarEsdevenimentsAmbPreu(array $events): array
    {
        $out = [];
        foreach ($events as $ev) {
            if (! is_array($ev)) {
                continue;
            }
            if ($this->discoveryEventTePreu($ev)) {
                $out[] = $ev;
            }
        }

        return $out;
    }

    private function calculateGeoPoint(float $lat, float $lng): string
    {
        return round($lat, 4) . ',' . round($lng, 4);
    }

    private function transformEvents(array $data): array
    {
        $events = [];
        $embedded = [];
        if (isset($data['_embedded']) && is_array($data['_embedded'])) {
            $embedded = $data['_embedded'];
        }
        $eventItems = [];
        if (isset($embedded['events']) && is_array($embedded['events'])) {
            $eventItems = $embedded['events'];
        }

        foreach ($eventItems as $eventItem) {
            $events[] = $this->transformEventBasic($eventItem);
        }

        return $events;
    }

    private function transformEventBasic(array $event): array
    {
        $images = [];
        if (isset($event['images']) && is_array($event['images'])) {
            $images = $event['images'];
        }
        $imageUrl = $this->getImageUrl($images, '16:9');

        $dates = [];
        if (isset($event['dates']) && is_array($event['dates'])) {
            $dates = $event['dates'];
        }
        $start = [];
        if (isset($dates['start']) && is_array($dates['start'])) {
            $start = $dates['start'];
        }
        $eventDate = '';
        if (array_key_exists('localDate', $start)) {
            $eventDate = $start['localDate'];
        }
        $eventTime = '';
        if (array_key_exists('localTime', $start)) {
            $eventTime = $start['localTime'];
        }

        $classifications = [];
        if (isset($event['classifications']) && is_array($event['classifications'])) {
            $classifications = $event['classifications'];
        }
        $category = 'Esdeveniment';
        if (isset($classifications[0]['segment']['name'])) {
            $category = $classifications[0]['segment']['name'];
        }

        $venues = [];
        if (isset($event['_embedded']['venues']) && is_array($event['_embedded']['venues'])) {
            $venues = $event['_embedded']['venues'];
        }
        $venue = [];
        if (isset($venues[0]) && is_array($venues[0])) {
            $venue = $venues[0];
        }
        $venueName = '';
        if (array_key_exists('name', $venue)) {
            $venueName = $venue['name'];
        }
        $city = '';
        if (isset($venue['city']) && is_array($venue['city']) && array_key_exists('name', $venue['city'])) {
            $city = $venue['city']['name'];
        }

        $priceRanges = [];
        if (isset($event['priceRanges']) && is_array($event['priceRanges'])) {
            $priceRanges = $event['priceRanges'];
        }
        $minPrice = null;
        $maxPrice = null;
        $moneda = 'EUR';
        if (isset($priceRanges[0]) && is_array($priceRanges[0])) {
            $pr0 = $priceRanges[0];
            if (array_key_exists('min', $pr0)) {
                $minPrice = $pr0['min'];
            }
            if (array_key_exists('max', $pr0)) {
                $maxPrice = $pr0['max'];
            }
            if (array_key_exists('currency', $pr0)) {
                $moneda = $pr0['currency'];
            }
        }

        $urlEv = '';
        if (array_key_exists('url', $event)) {
            $urlEv = $event['url'];
        }

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
                'moneda' => $moneda,
            ],
            'url' => $urlEv,
        ];
    }

    private function transformEventDetail(array $event): array
    {
        $basic = $this->transformEventBasic($event);

        $basic['descripcio'] = '';
        if (array_key_exists('pleaseNote', $event)) {
            $basic['descripcio'] = $event['pleaseNote'];
        }
        $basic['informacio'] = '';
        if (array_key_exists('info', $event)) {
            $basic['informacio'] = $event['info'];
        }

        $images = [];
        if (isset($event['images']) && is_array($event['images'])) {
            $images = $event['images'];
        }
        $basic['imatge_gran'] = $this->getImageUrl($images, '16:9');

        $seatmap = [];
        if (isset($event['seatmap']) && is_array($event['seatmap'])) {
            $seatmap = $event['seatmap'];
        }
        $basic['mapa_seients'] = '';
        if (array_key_exists('staticUrl', $seatmap)) {
            $basic['mapa_seients'] = $seatmap['staticUrl'];
        }

        return $basic;
    }

    private function transformEventFull(array $event): array
    {
        $basic = $this->transformEventBasic($event);

        $basic['descripcio'] = '';
        if (array_key_exists('pleaseNote', $event)) {
            $basic['descripcio'] = $event['pleaseNote'];
        }
        $basic['informacio'] = '';
        if (array_key_exists('info', $event)) {
            $basic['informacio'] = $event['info'];
        }
        $basic['url_oficial'] = '';
        if (array_key_exists('url', $event)) {
            $basic['url_oficial'] = $event['url'];
        }

        $images = [];
        if (isset($event['images']) && is_array($event['images'])) {
            $images = $event['images'];
        }
        $basic['imatge_gran'] = $this->getImageUrl($images, '16:9');
        $basic['imatges'] = $this->transformAllImages($images);

        $seatmap = [];
        if (isset($event['seatmap']) && is_array($event['seatmap'])) {
            $seatmap = $event['seatmap'];
        }
        $basic['mapa_seients'] = '';
        if (array_key_exists('staticUrl', $seatmap)) {
            $basic['mapa_seients'] = $seatmap['staticUrl'];
        }

        $venues = [];
        if (isset($event['_embedded']['venues']) && is_array($event['_embedded']['venues'])) {
            $venues = $event['_embedded']['venues'];
        }
        if (! empty($venues)) {
            $basic['venue'] = $this->transformVenue($venues[0]);
        }

        $classifications = [];
        if (isset($event['classifications']) && is_array($event['classifications'])) {
            $classifications = $event['classifications'];
        }
        $basic['classificacions'] = $this->transformClassifications($classifications);

        $priceRanges = [];
        if (isset($event['priceRanges']) && is_array($event['priceRanges'])) {
            $priceRanges = $event['priceRanges'];
        }
        $basic['preus_complets'] = $this->transformPriceRanges($priceRanges);

        $attractions = [];
        if (isset($event['_embedded']['attractions']) && is_array($event['_embedded']['attractions'])) {
            $attractions = $event['_embedded']['attractions'];
        }
        $basic['artistes'] = $this->transformAttractions($attractions);

        $basic['accessibilitat'] = null;
        if (array_key_exists('accessibility', $event)) {
            $basic['accessibilitat'] = $event['accessibility'];
        }
        $basic['aparcament'] = null;
        if (array_key_exists('parking', $event)) {
            $basic['aparcament'] = $event['parking'];
        }
        $basic['portes_info'] = null;
        if (array_key_exists('doorsInfo', $event)) {
            $basic['portes_info'] = $event['doorsInfo'];
        }

        $organitzadors = [];
        if (isset($event['promoters']) && is_array($event['promoters'])) {
            foreach ($event['promoters'] as $pr) {
                if (is_array($pr) && isset($pr['name']) && $pr['name'] !== '') {
                    $organitzadors[] = ['nom' => $pr['name']];
                }
            }
        }
        if (empty($organitzadors) && isset($event['promoter']) && is_array($event['promoter'])) {
            if (isset($event['promoter']['name']) && $event['promoter']['name'] !== '') {
                $organitzadors[] = ['nom' => $event['promoter']['name']];
            }
        }
        $basic['organitzadors'] = $organitzadors;

        if (! empty($basic['venue']) && is_array($basic['venue'])) {
            $v = $basic['venue'];
            if ($basic['ubicacio']['recinte'] === '' && isset($v['nom']) && $v['nom'] !== '') {
                $basic['ubicacio']['recinte'] = $v['nom'];
            }
            if ($basic['ubicacio']['ciutat'] === '' && isset($v['ciutat']) && $v['ciutat'] !== '') {
                $basic['ubicacio']['ciutat'] = $v['ciutat'];
            }
        }

        return array_merge($basic, $this->computeDisponibilitatVenda($event));
    }

    /**
     * Deriva si es pot comprar (entrada general) segons Discovery API.
     * Conservador: cancel·lat, ajornat, offsale, fora de finestra public.start/end.
     */
    private function computeDisponibilitatVenda(array $eventRaw): array
    {
        $dates = [];
        if (isset($eventRaw['dates']) && is_array($eventRaw['dates'])) {
            $dates = $eventRaw['dates'];
        }
        $status = [];
        if (isset($dates['status']) && is_array($dates['status'])) {
            $status = $dates['status'];
        }
        $code = '';
        if (array_key_exists('code', $status)) {
            $code = $status['code'];
        }

        if (in_array($code, ['cancelled', 'postponed'], true)) {
            return [
                'dates_status_code' => $code,
                'esgotat' => true,
                'pot_comprar' => false,
                'motiu_disponibilitat' => $code,
            ];
        }

        if ($code === 'offsale') {
            return [
                'dates_status_code' => $code,
                'esgotat' => true,
                'pot_comprar' => false,
                'motiu_disponibilitat' => 'offsale',
            ];
        }

        $esgotat = false;
        $motiu = null;

        $sales = [];
        if (isset($eventRaw['sales']) && is_array($eventRaw['sales'])) {
            $sales = $eventRaw['sales'];
        }
        $public = [];
        if (isset($sales['public']) && is_array($sales['public'])) {
            $public = $sales['public'];
        }
        $end = null;
        if (array_key_exists('endDateTime', $public)) {
            $end = $public['endDateTime'];
        }
        if ($end !== null && $end !== '') {
            $endTs = strtotime($end);
            if ($endTs !== false && $endTs < time()) {
                $esgotat = true;
                $motiu = 'venda_tancada';
            }
        }

        $start = null;
        if (array_key_exists('startDateTime', $public)) {
            $start = $public['startDateTime'];
        }
        if (! $esgotat && $start !== null && $start !== '') {
            $startTs = strtotime($start);
            if ($startTs !== false && $startTs > time()) {
                $esgotat = true;
                $motiu = 'venda_encara_no_oberta';
            }
        }

        $potComprar = true;
        if ($esgotat) {
            $potComprar = false;
        }

        return [
            'dates_status_code' => $code,
            'esgotat' => $esgotat,
            'pot_comprar' => $potComprar,
            'motiu_disponibilitat' => $motiu,
        ];
    }

    /**
     * Preu unitari mínim per calcular import (entrades generals).
     */
    public function preuUnitariPerCompra(array $detallEsdeveniment): ?float
    {
        $preu = [];
        if (isset($detallEsdeveniment['preu']) && is_array($detallEsdeveniment['preu'])) {
            $preu = $detallEsdeveniment['preu'];
        }
        if (isset($preu['min']) && $preu['min'] !== null && $preu['min'] !== '') {
            return round((float) $preu['min'], 2);
        }
        $ranges = [];
        if (isset($detallEsdeveniment['preus_complets']) && is_array($detallEsdeveniment['preus_complets'])) {
            $ranges = $detallEsdeveniment['preus_complets'];
        }
        foreach ($ranges as $r) {
            if (isset($r['min']) && $r['min'] !== null && $r['min'] !== '') {
                return round((float) $r['min'], 2);
            }
        }

        return null;
    }

    private function transformAllImages(array $images): array
    {
        $result = [];
        foreach ($images as $image) {
            $ratio = '';
            if (array_key_exists('ratio', $image)) {
                $ratio = $image['ratio'];
            }
            $url = '';
            if (array_key_exists('url', $image)) {
                $url = $image['url'];
            }
            $width = null;
            if (array_key_exists('width', $image)) {
                $width = $image['width'];
            }
            $height = null;
            if (array_key_exists('height', $image)) {
                $height = $image['height'];
            }
            $result[] = [
                'ratio' => $ratio,
                'url' => $url,
                'width' => $width,
                'height' => $height,
            ];
        }
        return $result;
    }

    private function transformVenue(array $venue): array
    {
        $address = [];
        if (isset($venue['address']) && is_array($venue['address'])) {
            $address = $venue['address'];
        }
        $city = [];
        if (isset($venue['city']) && is_array($venue['city'])) {
            $city = $venue['city'];
        }
        $country = [];
        if (isset($venue['country']) && is_array($venue['country'])) {
            $country = $venue['country'];
        }
        $location = [];
        if (isset($venue['location']) && is_array($venue['location'])) {
            $location = $venue['location'];
        }

        $latitud = null;
        if (isset($location['latitude'])) {
            $latitud = (float) $location['latitude'];
        }
        $longitud = null;
        if (isset($location['longitude'])) {
            $longitud = (float) $location['longitude'];
        }

        $nom = '';
        if (array_key_exists('name', $venue)) {
            $nom = $venue['name'];
        }
        $adreca = '';
        if (array_key_exists('line1', $address)) {
            $adreca = $address['line1'];
        }
        $ciutatNom = '';
        if (array_key_exists('name', $city)) {
            $ciutatNom = $city['name'];
        }
        $paisNom = '';
        if (array_key_exists('name', $country)) {
            $paisNom = $country['name'];
        }
        $codiPostal = '';
        if (array_key_exists('postalCode', $city)) {
            $codiPostal = $city['postalCode'];
        }
        $capacitat = null;
        if (array_key_exists('capacity', $venue)) {
            $capacitat = $venue['capacity'];
        }

        return [
            'nom' => $nom,
            'adreca' => $adreca,
            'ciutat' => $ciutatNom,
            'pais' => $paisNom,
            'codi_postal' => $codiPostal,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'capacitat' => $capacitat,
        ];
    }

    private function transformClassifications(array $classifications): array
    {
        $result = [];
        foreach ($classifications as $class) {
            $segment = '';
            if (isset($class['segment']['name'])) {
                $segment = $class['segment']['name'];
            }
            $genre = '';
            if (isset($class['genre']['name'])) {
                $genre = $class['genre']['name'];
            }
            $subgenre = '';
            if (isset($class['subGenre']['name'])) {
                $subgenre = $class['subGenre']['name'];
            }
            $type = '';
            if (array_key_exists('type', $class)) {
                $type = $class['type'];
            }
            $subtype = '';
            if (isset($class['subType']['name'])) {
                $subtype = $class['subType']['name'];
            }
            $result[] = [
                'segment' => $segment,
                'genre' => $genre,
                'subgenre' => $subgenre,
                'type' => $type,
                'subtype' => $subtype,
            ];
        }
        return $result;
    }

    private function transformPriceRanges(array $priceRanges): array
    {
        $result = [];
        foreach ($priceRanges as $price) {
            $tipus = '';
            if (array_key_exists('type', $price)) {
                $tipus = $price['type'];
            }
            $min = null;
            if (array_key_exists('min', $price)) {
                $min = $price['min'];
            }
            $max = null;
            if (array_key_exists('max', $price)) {
                $max = $price['max'];
            }
            $moneda = 'EUR';
            if (array_key_exists('currency', $price)) {
                $moneda = $price['currency'];
            }
            $result[] = [
                'tipus' => $tipus,
                'min' => $min,
                'max' => $max,
                'moneda' => $moneda,
            ];
        }
        return $result;
    }

    private function transformAttractions(array $attractions): array
    {
        $result = [];
        foreach ($attractions as $attraction) {
            $images = [];
            if (isset($attraction['images']) && is_array($attraction['images'])) {
                $images = $attraction['images'];
            }
            $imatgeAttr = '';
            if (! empty($images)) {
                $imatgeAttr = $this->getImageUrl($images, '16:9');
            }
            $idAttr = '';
            if (array_key_exists('id', $attraction)) {
                $idAttr = $attraction['id'];
            }
            $nomAttr = '';
            if (array_key_exists('name', $attraction)) {
                $nomAttr = $attraction['name'];
            }
            $result[] = [
                'id' => $idAttr,
                'nom' => $nomAttr,
                'imatge' => $imatgeAttr,
            ];
        }
        return $result;
    }

    private function getImageUrl(array $images, string $ratio = '16:9'): string
    {
        foreach ($images as $image) {
            if (isset($image['ratio']) && $image['ratio'] === $ratio && isset($image['url'])) {
                return $image['url'];
            }
        }

        if (! empty($images) && isset($images[0]) && is_array($images[0]) && array_key_exists('url', $images[0])) {
            return $images[0]['url'];
        }

        return '';
    }
}