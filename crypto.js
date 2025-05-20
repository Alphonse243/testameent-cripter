// Utilise Web Crypto API pour AES-256-CBC et SHA-256

// Convert string to ArrayBuffer
function str2ab(str) {
    return new TextEncoder().encode(str);
}

// Convert ArrayBuffer to string
function ab2str(buf) {
    return new TextDecoder().decode(buf);
}

// Convert ArrayBuffer to base64
function ab2b64(buf) {
    return btoa(String.fromCharCode(...new Uint8Array(buf)));
}

// Convert base64 to ArrayBuffer
function b642ab(b64) {
    const bin = atob(b64);
    const buf = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
    return buf.buffer;
}

// Derive a 256-bit key from passphrase using SHA-256
async function deriveKey(passphrase) {
    const hash = await crypto.subtle.digest('SHA-256', str2ab(passphrase));
    return crypto.subtle.importKey(
        'raw', hash, { name: 'AES-CBC' }, false, ['encrypt', 'decrypt']
    );
}

// Encrypt message with AES-256-CBC, IV prepended, output base64
async function encryptTestament(message, passphrase) {
    const key = await deriveKey(passphrase);
    const iv = crypto.getRandomValues(new Uint8Array(16));
    const ciphertext = await crypto.subtle.encrypt(
        { name: 'AES-CBC', iv: iv },
        key,
        str2ab(message)
    );
    // Concatenate IV + ciphertext
    const combined = new Uint8Array(iv.length + ciphertext.byteLength);
    combined.set(iv, 0);
    combined.set(new Uint8Array(ciphertext), iv.length);
    return ab2b64(combined.buffer);
}

// Decrypt base64 (IV + ciphertext) with AES-256-CBC
async function decryptTestament(b64, passphrase) {
    const data = new Uint8Array(b642ab(b64));
    const iv = data.slice(0, 16);
    const ciphertext = data.slice(16);
    const key = await deriveKey(passphrase);
    try {
        const plaintext = await crypto.subtle.decrypt(
            { name: 'AES-CBC', iv: iv },
            key,
            ciphertext
        );
        return ab2str(plaintext);
    } catch (e) {
        return false;
    }
}
