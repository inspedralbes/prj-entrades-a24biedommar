## Why

Per completar el Sprint 1 del projecte TR3 TicketMaster, cal integrar tots els components desenvolupats durant els dies anteriors i verificar que funcionen correctament junts. Això inclou el flux complet des del Login fins a la Landing passant per la Cua Virtual.

## What Changes

- **S1.14**: Integrar Login → Cua → Landing - Verificar flux complet d'autenticació i entrada a la cua
- **S1.15**: Verificació i testing Sprint 1 - Testejar totes les funcionalitats implementades

## Capabilities

### New Capabilities
- `integration-login-queue-landing`: Integració completa del flux d'usuari des de l'autenticació fins a la cua virtual i la landing
- `sprint1-verification`: Verificació i testing de totes les funcionalitats del Sprint 1

### Modified Capabilities
- `login-page`: Flux de redirecció cap a la cua o landing
- `queue-page`: Integració amb autenticació i entrada des de login
- `landing-page`: Accés des de login美化 i redirecció segons estat

## Impact

- **Backend**: Ajustos en控制系统 de redirecció i flux d'autenticació
- **Frontend**: Integració de components de Login, Cua i Landing
- **Testing**: Verificació completa del Sprint 1

## Verificació de Codi segons Agents

Totes les funcionalitats implementades han de seguir les directrius dels agents:
- **AgentLaravel**: Backend Laravel 13 amb PHP 8.3+, PostgreSQL, Redis, estil de codi sans ternaris
- **AgentNuxt**: Frontend Nuxt 4 amb Vue 3, Pinia, Tailwind CSS, estils DICE
- **AgentNode**: Node.js 24 per a la cua virtual
- **AgentRedis**: Redis 8.6.1 per a la gestió de cues