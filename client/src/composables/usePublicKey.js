import { ref } from 'vue'

const publicKeyPem = ref(null)

export function usePublicKey() {
    const fetchPublicKey = async () => {
        try {
            const response = await fetch(`${import.meta.env.BASE_URL}keys/public.pem`)
            publicKeyPem.value = await response.text()
        } catch (error) {
            console.error('Error fetching public key:', error)
        }
    }

    return {
        publicKeyPem,
        fetchPublicKey
    }
}