## Context

Projecte TR3 TicketMaster amb sistema d'autenticació parcialment implementat (backend Laravel + Sanctum). Cal completar la part frontend per permetre als usuaris iniciar sessió, enregistrar-se i navegar per l'aplicació de forma segura.

## Goals / Non-Goals

**Goals:**
- Implementar sistema de redirecció return_to al backend Laravel
- Crear Store Pinia per gestionar estat d'autenticació (token, usuari, perfil)
- Crear pàgines Login/Registre amb estils DICE (fons negre, botons neó #FF0055, Electric Blue #00F0FF)
- Implementar middleware de navegació per protegir rutes segons estat d'autenticació

**Non-Goals:**
- No s'implementa sistema de "recordar-me" (Remember Me)
- No s'implementa autenticació de dos factors (2FA)
- No s'implementa recuperació de contrasenya

## Decisions

1. **Estat Pinia vs composables**: S'usa Pinia per ser l'estat oficial de Nuxt 4 i per integració amb DevTools.

2. **Estils CSS**: Utilitzar estils DICE definits (neó #FF0055, Electric Blue #00F0FF) amb fons negres.

3. **Return-to-redirect**: Guardar a sessió Laravel (no cookie) per seguretat.

4. **Middleware Nuxt**: Implementar com middleware de navegació amb suport per a rutes públiques/privades.

## Risks / Trade-offs

- [Risk] Token expirat durant navegació → Mitigació:Middleware redirigeix a login
- [Risk] Estat Pinia no persisteix en refresh → Mitigació: Guardar token a cookie accessible des de SSR