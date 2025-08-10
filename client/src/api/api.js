import axios from 'axios';
import { usePublicKey } from '@/composables/usePublicKey';
import { encryptWithPublicKey } from '@/encrypt';
import CryptoJS from 'crypto-js';

const { publicKeyPem } = usePublicKey();

const obfuscate = (payloads) => {
    const stringifiedPayloads = JSON.stringify(payloads);
    const encryptedPayloads = encryptWithPublicKey(publicKeyPem.value, stringifiedPayloads);

    const secretKey = import.meta.env.VITE_APP_OBFUSCATION_KEY;
    const signature = CryptoJS.HmacSHA256(encryptedPayloads, secretKey).toString();

    return {
        encryptedPayloads,
        signature,
    };
};

/**
 * Public API request function
 * @param {string} method - HTTP method (get, post, put, delete)
 * @param {string} apiUrl - Endpoint URL
 * @param {object} data - Data payload (must include payloads property for encryption)
 * @param {boolean} file - Whether the request is a file upload (multipart/form-data)
 */
export const publicApi = async (method, apiUrl, data = {}, files = null) => {
    const { encryptedPayloads, signature } = obfuscate(data?.payloads);
    let formData = new FormData();
    formData.append("data", encryptedPayloads);

    if (files){
        files.forEach(file => {
            formData.append("images[]", file);
        });
    }

    const apiInstance = axios.create({
        baseURL: import.meta.env.VITE_APP_API_URL,
        headers: {
            'Content-Type': files ? 'multipart/form-data':'application/json',
            'X-Signature': signature,
        },
    });

    // Prepare request body
    const requestBody = formData;

    // Execute request dynamically based on method
    switch (method.toLowerCase()) {
        case 'get':
            return apiInstance.get(apiUrl, { params: requestBody });
        case 'post':
            return apiInstance.post(apiUrl, requestBody);
        case 'put':
            return apiInstance.put(apiUrl, requestBody);
        case 'delete':
            return apiInstance.delete(apiUrl, { data: requestBody });
        default:
            throw new Error(`Unsupported HTTP method: ${method}`);
    }
};

// Example usage:
export const getProducts = (data) => {return publicApi('get', `/api/products${data.params??''}`, data)};
export const addProduct = (data, files) => {return publicApi('post', '/api/products', data, files)};
