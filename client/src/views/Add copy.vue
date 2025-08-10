<template>
    <form @submit="handleSubmit">
        <div>
            <InputGroup>
                <InputText v-model="owner" placeholder="Owner" />
            </InputGroup>
            
            <InputGroup>
                <InputText v-model="product" placeholder="Product" />
            </InputGroup>
            
            <div class="card">
                <FileUpload 
                    ref="fileUploadRef"
                    name="images[]" 
                    :customUpload="true"
                    :multiple="true" 
                    accept="image/*"
                    @select="onFileSelect"
                >
                    <template #empty>
                        <span>Drag and drop files here to upload.</span>
                    </template>
                </FileUpload>
            </div>
        </div>
        <div class="card flex justify-center">
            <Button type="submit" label="Submit" />
        </div>
    </form>
</template>

<script setup>
import InputGroup from 'primevue/inputgroup';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { addProduct } from '@/api/api.js';
import { encryptWithPublicKey } from '@/encrypt';
import CryptoJS from 'crypto-js';

const fileUploadRef = ref(null);
const owner = ref('');
const product = ref('');
const selectedFiles = ref([]);
const publicKeyPem = ref('');

const onFileSelect = (event) => {
    selectedFiles.value = event.files;
};

onMounted(() => {
    fetch(`${import.meta.env.BASE_URL}keys/public.pem`)
      .then(res => res.text())
      .then(data => {
        publicKeyPem.value = data;
      });
});

const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const payload = JSON.stringify({
        owner: owner.value,
        product: product.value,
      });
      const encrypted = encryptWithPublicKey(publicKeyPem.value, payload);

      const secretKey = import.meta.env.VITE_APP_OBFUSCATION_KEY;
      const signature = CryptoJS.HmacSHA256(encrypted, secretKey).toString();

      const res = await axios.post(
        `${import.meta.env.VITE_APP_API_URL}/api/products`,
        { data: encrypted },
        { headers: {
          'Content-Type': 'application/json',
          'X-Signature': signature,
        }}
      );

      console.log(res.data);
    }
    catch (error) {
      console.error("Error submitting form:", error);
    }
  }
</script>
