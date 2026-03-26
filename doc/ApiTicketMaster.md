# Ticketmaster International Discovery API (v2.0)

## Dades d'accés (projecte)

- Usuari: `a24biedommar@inspedralbes.cat`
- Contrasenya: `Evi8p3w98!h6GF#`
- Base URL: `https://app.ticketmaster.eu/mfxapi/v2/`
- Format recomanat: `application/json`
- Seguretat obligatoria: `TLS v1.2+`

> Nota: Ticketmaster indica que ja **no accepta noves sol·licituds de clau API** per a la International Discovery API. Per noves integracions, recomana Discovery API.

---

## Autenticació

Cal enviar la clau API a **totes** les peticions com a query param:

- `apikey=WXrtWLRrAetbPfcMJi5puxJd4a9YFA69`

Exemple:

```http
GET /events?domain=spain&apikey=YOUR_API_KEY HTTP/1.1
Host: app.ticketmaster.eu
Accept: application/json
```

---

## Mercats suportats

Germany, Austria, Netherlands, Denmark, Belgium, Norway, Switzerland, Spain, Sweden, Finland, Poland, UAE, UK (Ticketweb.co.uk) i Canada (Admission.com).

No inclou: UK/Ireland de `ticketmaster.co.uk` / `ticketmaster.ie` ni `ticketmaster.com` (USA + resta de Canada).

---

## Canvis principals de V2

- Noms de parametres amb underscore: `venue_name`, `event_date`, `seats_available`.
- `domain_id` / `domain_ids` substituits per `domain`.
- Events/venues externs inclosos per defecte (`exclude_external` inverteix la logica antiga).
- Rutes actualitzades:
  - Event details: `/events/{id}` (abans `/event/{id}`)
  - Venue details: `/venues/{id}` (abans `/venue/{id}`)
  - Attraction details: `/attractions/{id}` (abans `/attraction/{id}`)
  - Similar attractions: `/attractions/{attraction_id}/similar`
- `is_not_cancelled` -> `cancelled` (logica invertida).
- `is_seats_available` -> `seats_available`.
- `radius_unit` eliminat a Venue Search.
- `images` canvia de llista a objecte.
- Canvi de format de data local en events.

---

## Resum de serveis

1. **Event Service**
   - Event Search
   - Event Details
   - Event Prices
   - Event Seatmap
   - Event Areas
2. **Attraction Service**
   - Attraction Search
   - Attraction Details
   - Attraction Suggestions
   - Similar Attractions
3. **Venue Service**
   - Venue Search
   - Venue Details
4. **Information Service**
   - Countries List
   - Domains List
   - Cities List
   - Categories List
5. **Search Suggest Service**
   - Search Suggestions

---

## 1) Event Service

### 1.1 Event Search

- **Endpoint**: `GET /events`
- **URL completa**: `https://app.ticketmaster.eu/mfxapi/v2/events`

Parametres destacats:

- `domain` (recomanat; es poden passar multiples)
- `lang`
- `event_ids`, `attraction_ids`
- `category_ids`, `subcategory_ids`
- `event_name`
- `venue_ids`, `city_ids`, `country_ids`
- `postal_code`, `lat`, `long`, `radius`
- `eventdate_from`, `eventdate_to`
- `onsaledate_from`, `onsaledate_to`
- `offsaledate_from`, `offsaledate_to`
- `min_price`, `max_price`, `price_excl_fees`
- `seats_available`, `cancelled`, `rescheduled`, `is_not_package`
- `exclude_external`
- `sort_by` (`eventname|popularity|eventdate|proximity|onsaledate`)
- `order` (`asc|desc`)
- `rows` (max `250`), `start`

Exemple:

```http
GET /events?domain=spain&lang=es-es&rows=20&start=0&sort_by=eventdate&order=asc&apikey=YOUR_API_KEY HTTP/1.1
Host: app.ticketmaster.eu
Accept: application/json
```

### 1.2 Event Details

- **Endpoint**: `GET /events/{event_id}`
- Params: `event_id` (required), `domain` (recomanat), `lang`

### 1.3 Event Prices

- **Endpoint**: `GET /events/{event_id}/prices`
- Params: `event_id` (required), `domain` (required), `lang`, `price_level_ids`

### 1.4 Event Seatmap

- **Endpoint**: `GET /events/{event_id}/seatmap`
- Params: `event_id` (required), `domain` (required)

### 1.5 Event Areas

- **Endpoint**: `GET /events/{event_id}/areas`
- Params: `event_id` (required), `domain` (required), `lang`

---

## 2) Attraction Service

### 2.1 Attraction Search

- **Endpoint**: `GET /attractions`
- Params principals:
  - `domain`, `lang`
  - `attraction_name`, `attraction_ids`
  - `has_events`
  - `query`
  - `category_ids`, `subcategory_ids`
  - `sort_by` (`attraction_name|popularity`)
  - `order` (`asc|desc`)
  - `rows` (max `250`), `start`

### 2.2 Attraction Details

- **Endpoint**: `GET /attractions/{attraction_id}`
- Params: `attraction_id` (required), `domain` (recomanat), `lang`

### 2.3 Attraction Suggestions

- **Endpoint**: `GET /attractions/suggestions`
- Params: `attraction_name` (minim 3 caracters, required), `lang`, `domain`, `has_events`

### 2.4 Similar Attractions

- **Endpoint**: `GET /attractions/{attraction_id}/similar`
- Params: `attraction_id` (required), `lang`, `domain`, `has_events`, `rows`

---

## 3) Venue Service

### 3.1 Venue Search

- **Endpoint**: `GET /venues`
- Params principals:
  - `domain` (required)
  - `lang`
  - `venue_name`, `venue_ids`
  - `city_ids`, `postal_code`
  - `lat`, `long`, `radius`
  - `exclude_external`
  - `sort_by` (`venuename|cityname`)
  - `order` (`asc|desc`)
  - `rows` (max `250`), `start`

### 3.2 Venue Details

- **Endpoint**: `GET /venues/{venue_id}`
- Params: `venue_id` (required), `domain` (recomanat), `lang`

---

## 4) Information Service

### 4.1 Countries List

- **Endpoint**: `GET /countries`
- Params: `domain`, `lang`

### 4.2 Domains List

- **Endpoint**: `GET /domains`
- Params: `country_id`

### 4.3 Cities List

- **Endpoint**: `GET /cities`
- Params: `country_id`, `domain`, `lang`

### 4.4 Categories List

- **Endpoint**: `GET /categories`
- Params: `lang`, `domain`, `category_id`, `subcategories`

---

## 5) Search Suggest Service

### Search Suggestions

- **Endpoint**: `GET /search/suggestions`
- Params:
  - `query` (required, minim 2 caracters)
  - `sort_by`, `lang`, `order`
  - `domain`
  - `exclude_types`
  - `exclude_external`
  - `include_packages`

---

## Dates i formats

- Dates en UTC.
- Format recomanat de data: `yyyy-MM-dd'T'HH:mm:ssZ`
- `Accept` header: `application/json`

---

## Exemple generic JavaScript (XMLHttpRequest)

```javascript
var request = new XMLHttpRequest();

request.open(
  'GET',
  'https://app.ticketmaster.eu/mfxapi/v2/events?domain=spain&rows=10&start=0&apikey=YOUR_API_KEY'
);

request.setRequestHeader('Accept', 'application/json');

request.onreadystatechange = function () {
  if (this.readyState === 4) {
    console.log('Status:', this.status);
    console.log('Headers:', this.getAllResponseHeaders());
    console.log('Body:', this.responseText);
  }
};

request.send();
```

---

## Checklist d'implementacio rapida

- Tenir `apikey` valid i activa.
- Enviar sempre peticions via HTTPS.
- Afegir `Accept: application/json`.
- Fixar `domain` explicitament (molt recomanat).
- Paginacio amb `rows` + `start`.
- Controlar errors HTTP i limitacions del mercat.
