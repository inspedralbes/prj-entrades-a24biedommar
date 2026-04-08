## Why

The application needs a virtual queue system ("The Gatekeeper") to manage user access to high-demand events. Without a queue, all users compete simultaneously for limited seats, causing race conditions, system overload, and poor user experience. This change implements a managed queue with real-time position tracking.

## What Changes

- **S1.11**: Create Node.js service with Socket.IO for queue management - validates users, generates turn tokens, manages queue positions
- **S1.12**: Implement Redis pub/sub connection between Laravel and Node.js - Laravel publishes queue state changes, Node.js subscribes to manage real-time updates
- **S1.13**: Build "Waiting Room" frontend page with WebSocket connection - displays position counter, flip animation effect, real-time updates

## Capabilities

### New Capabilities

- `gatekeeper-queue-logic`: Node.js service managing queue state, user validation, turn token generation, position tracking
- `redis-queue-bridge`: Laravel-Redis integration for publishing queue state changes to Node.js subscribers
- `waiting-room-ui`: Nuxt 4 page with real-time queue position display, flip countdown animation, WebSocket connectivity

### Modified Capabilities

- None - this is a net-new feature set

## Impact

- **Backend**: New Node.js service at `server/gatekeeper/`
- **Queue System**: Redis pub/sub channels for queue state
- **Frontend**: New `/waiting-room` route in Nuxt 4 app
- **Dependencies**: Socket.IO, Redis client, Node.js 24