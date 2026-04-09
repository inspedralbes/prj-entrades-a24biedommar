# 🔮 Futuros Cambios

## 1. Eliminación del Sistema de Colas

Eliminar completamente el sistema de colas del proyecto:

- Eliminar la lógica de colas en tiempo real (Socket.IO relacionado con colas).
- Eliminar la gestión de colas en Redis.
- Eliminar los componentes de frontend relacionados con la cola de espera.
- Eliminar los endpoints del backend que gestionan la cola.

---

## 2. Integración con la API de Ticketmaster

Conectar el proyecto directamente con la API oficial de Ticketmaster para obtener el **100% de la información por evento**:

- Autenticación con la API de Ticketmaster (API Key).
- Llamadas a la Discovery API para obtener todos los eventos disponibles.
- Extraer y almacenar todos los campos por evento:
  - Nombre, fecha, hora, lugar, ciudad, país.
  - Imágenes, descripción, género y subgénero.
  - Precios, tipos de entradas y disponibilidad.
  - URL oficial del evento.
  - Artistas / atracciones vinculadas.
  - Información del venue (aforo, dirección, coordenadas).
  - Clasificaciones y etiquetas.
- Sincronización periódica de datos (cron job o bajo demanda).
