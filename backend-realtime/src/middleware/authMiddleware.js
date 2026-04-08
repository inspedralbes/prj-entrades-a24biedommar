//================================ NAMESPACES / IMPORTS ============
const jwt = require('jsonwebtoken');

//================================ VARIABLES / CONSTANTS ============

const JWT_SECRET = process.env.JWT_SECRET || 'ticketmaster-secret-key';

/**
 * URL base de l'API Laravel per validar tokens Sanctum (handshake Socket.IO).
 */
const LARAVEL_API_URL = (process.env.LARAVEL_API_URL || 'http://localhost:8000').replace(/\/$/, '');

//================================ MIDDLEWARE / LÒGICA ================

/**
 * Intenta validar com a JWT de sessió (compatibilitat amb proves i tokens propis).
 *
 * @param {string} token
 * @returns {{ valid: true, userId: string, email: string, role: string } | { valid: false, error: string } | null}
 */
function intentarJwtSessio(token) {
    if (!token || typeof token !== 'string') {
        return { valid: false, error: 'Token no proporcionat' };
    }

    const parts = token.split('.');
    if (parts.length !== 3) {
        return null;
    }

    try {
        const decoded = jwt.verify(token, JWT_SECRET);

        if (decoded.token_type === 'turn' && decoded.turn_token === true) {
            return { valid: false, error: 'Turn token no vàlid per a connexió' };
        }

        return {
            valid: true,
            userId: String(decoded.sub || decoded.user_id || ''),
            email: decoded.email || '',
            role: decoded.role || 'client',
        };
    } catch {
        return null;
    }
}

/**
 * Valida un token Bearer de Laravel Sanctum cridant GET /api/usuari.
 *
 * @param {string} token
 * @returns {Promise<{ valid: true, userId: string, email: string, role: string } | { valid: false, error: string }>}
 */
async function intentarSanctum(token) {
    if (!token) {
        return { valid: false, error: 'Token no proporcionat' };
    }

    try {
        const resposta = await fetch(`${LARAVEL_API_URL}/api/usuari`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
            },
        });

        if (!resposta.ok) {
            return { valid: false, error: 'Token invàlid o expirat' };
        }

        const cos = await resposta.json();
        const u = cos.data !== undefined ? cos.data : cos;

        if (!u || u.id === undefined) {
            return { valid: false, error: 'Resposta d\'usuari invàlida' };
        }

        return {
            valid: true,
            userId: String(u.id),
            email: u.correu_electronic || '',
            role: u.rol || 'client',
        };
    } catch (error) {
        console.error('Error validant Sanctum amb Laravel:', error.message);
        return { valid: false, error: 'No s\'ha pogut validar el token amb l\'API' };
    }
}

/**
 * Valida el token del handshaking de Socket.IO (JWT propi o Sanctum via Laravel).
 *
 * @param {object} socket - Socket de Socket.IO
 * @returns {Promise<{ valid: true, userId: string, email: string, role: string } | { valid: false, error: string }>}
 */
async function validarTokenHandshake(socket) {
    const token = socket.handshake.auth.token || socket.handshake.query.token;

    const jwtResultat = intentarJwtSessio(token);
    if (jwtResultat && jwtResultat.valid === true) {
        return jwtResultat;
    }
    if (jwtResultat && jwtResultat.valid === false && jwtResultat.error !== undefined) {
        if (jwtResultat.error !== 'Token no proporcionat') {
            return jwtResultat;
        }
    }

    return intentarSanctum(token);
}

/**
 * Funció per validar token des d'un event de socket (només JWT de sessió).
 *
 * @param {string} token - Token JWT
 * @returns {object|null} Dades de l'usuari si vàlid
 */
function validarToken(token) {
    if (!token) {
        return null;
    }

    try {
        const decoded = jwt.verify(token, JWT_SECRET);

        if (decoded.token_type === 'turn' && decoded.turn_token === true) {
            return null;
        }

        return {
            userId: decoded.sub || decoded.user_id,
            email: decoded.email,
            role: decoded.role,
        };
    } catch {
        return null;
    }
}

//================================ EXPORTS ==========================

module.exports = {
    validarTokenHandshake,
    validarToken,
    JWT_SECRET,
};
