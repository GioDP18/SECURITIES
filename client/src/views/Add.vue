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
import CryptoJS from 'crypto-js';
import { addProduct } from '@/api/api.js';

const fileUploadRef = ref(null);
const owner = ref('');
const product = ref('');
const selectedFiles = ref([]);
const publicKeyPem = ref('');

const onFileSelect = (event) => {
    selectedFiles.value = event.files;
};

const handleSubmit = async (e) => {
    e.preventDefault();
    try{
        let data = {
            payloads: {
                owner: owner.value,
                product: product.value,
            }
        };

        const result = await addProduct(data, selectedFiles.value);
        if (result.status === 201) {
            console.log("Product added successfully:", result.data);
            product.value = '';
            owner.value = ''; 
            selectedFiles.value = [];
            fileUploadRef.value.clear();
        } else {
            console.error("Failed to add product:", result);
        }
    }
    catch(error) {
        console.error("Error submitting form:", error);
    }
}
</script>
