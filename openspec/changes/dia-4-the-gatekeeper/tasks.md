## 1. Gatekeeper Queue Logic (Node.js)

- [x] 1.1 Set up Node.js project structure in `server/gatekeeper/`
- [x] 1.2 Install Socket.IO and Redis dependencies
- [x] 1.3 Implement authentication token validation middleware
- [x] 1.4 Implement queue state management (in-memory with Redis sync)
- [x] 1.5 Implement turn token generation (JWT with user_id, event_id, expiry)
- [x] 1.6 Implement position management (join, leave, update positions)
- [x] 1.7 Implement per-event queue isolation using Socket.IO rooms
- [x] 1.8 Implement threshold N enforcement (queue limit)
- [x] 1.9 Create WebSocket event handlers (connection, disconnect, position-update, turn-granted)
- [x] 1.10 Write unit tests for queue logic

**Branch:** `S1.11-TheGatekeeper-NodeJS`

## 2. Redis Queue Bridge (Laravel → Node)

- [x] 2.1 Configure Redis connection in Laravel
- [x] 2.2 Create QueueEventService for publishing queue state changes
- [x] 2.3 Implement user-joined event publication
- [x] 2.4 Implement user-left event publication
- [x] 2.5 Implement threshold-updated event publication
- [x] 2.6 Create queue controller endpoints for join/leave/status
- [x] 2.7 Implement API endpoint to check queue status for event
- [ ] 2.8 Write integration tests for Redis pub/sub

**Branch:** `S1.12-RedisPubSub`

## 3. Waiting Room UI (Frontend)

- [x] 3.1 Create `/waiting-room/[eventId]` route in Nuxt 4
- [x] 3.2 Install Socket.IO client
- [x] 3.3 Implement Socket.IO connection hook with reconnection logic
- [x] 3.4 Create position display component with animated counter
- [x] 3.5 Implement flip animation effect for waiting state
- [x] 3.6 Create queue status information panel (event name, time, position)
- [x] 3.7 Implement "Leave Queue" functionality with confirmation
- [x] 3.8 Implement "turn-granted" handling with "Enter Event" button
- [x] 3.9 Style with DICE theme (black background, neon pink #FF0055, electric blue #00F0FF)
- [ ] 3.10 Write component tests for Waiting Room

**Branch:** `S1.13-CuaVirtual-Frontend`

## 4. Integration & Testing

- [ ] 4.1 Test full flow: Login → Join Queue → Position Update → Turn Granted → Enter Event
- [ ] 4.2 Test WebSocket reconnection handling
- [ ] 4.3 Test multiple users in queue (manual or scripted)
- [ ] 4.4 Test queue full scenario with threshold
- [ ] 4.5 Verify all Socket.IO events working correctly
- [ ] 4.6 Run existing test suite to ensure no regressions