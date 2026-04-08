//================================ NAMESPACES / IMPORTS ============
const jwt = require('jsonwebtoken');

//================================ VARIABLES / CONSTANTS ============

const JWT_SECRET = process.env.JWT_SECRET || 'ticketmaster-secret-key';

//================================ MIDDLEWARE / LÒGICA ================

/**
 * Middleware de validació de token JWT per a Socket.IO
 * A. Extreu el token de la query o headers
 * B. Valida el token i desa les dades de l'usuari al socket
 * C. Rebutja connexió si el token és invàlid
 */

/**
 * Valida el token JWT del handshaking de Socket.IO
 * @param {object} socket - Socket de Socket.IO
 * @returns {object|null} Dades de l'usuari si vàlid, null altrament
 */
function validarTokenHandshake(socket) {
    // A. Extreure token de la query (handshake)
    const token = socket.handshake.auth.token || socket.handshake.query.token;
    
    if (!token) {
        return { valid: false, error: 'Token no proporcionat' };
    }

    // B. Validar token
    try {
        const decoded = jwt.verify(token, JWT_SECRET);
        
        // C. Comprovar que és un token d'autenticació vàlid (no turn token)
        if (decoded.token_type === 'turn' && decoded.turn_token === true) {
            return { valid: false, error: 'Turn token no vàlid per a connexió' };
        }

        return {
            valid: true,
            userId: decoded.sub || decoded.user_id,
            email: decoded.email,
            role: decoded.role
        };
    } catch (error) {
        return { valid: false, error: 'Token invàlid o expirat' };
    }
}

/**
 * Funció per validar token des d'un event de socket
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
            role: decoded.role
        };
    } catch (error) {
        return null;
    }
}

//================================ EXPORTS ==========================

module.exports = {
    validarTokenHandshake,
    validarToken,
    JWT_SECRET
};