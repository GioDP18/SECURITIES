import forge from 'node-forge';

export function encryptWithPublicKey(publicKeyPem, data) {
  const publicKey = forge.pki.publicKeyFromPem(publicKeyPem);
  const encrypted = publicKey.encrypt(forge.util.encodeUtf8(data), 'RSA-OAEP');
  return forge.util.encode64(encrypted); // Base64 encode for transport
}