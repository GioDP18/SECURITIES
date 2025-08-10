<template>
    <div>
        <InputGroup>
            <InputText placeholder="Keyword" v-model="searchKey" />
            <Button label="Search" />
        </InputGroup>
    </div>
    <Button @click="router.push({name: 'add'})">Add</Button>
    <h1>COUNT: {{products?.data?.length}}</h1>
    <div>
        <table>
            <thead>
                <tr>
                    <th>Owner</th>
                    <th>Product</th>
                    <th>Images</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products.data" :key="product.id">
                    <td>{{ product.owner }}</td>
                    <td>{{ product.product }}</td>
                    <td>
                        <div v-for="image in product.images" :key="image.id">
                            <img :src="BASE_URL+'/storage/'+image.path" alt="Product Image" style="width: 100px; height: 100px;" />
                        </div>
                    </td>
                    <td>
                        <Button @click="router.push({name: 'edit', params: {id: product.id}})" label="Edit" />
                        <Button @click="router.push({name: 'view', params: {id: product.id}})" label="View" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import InputText from 'primevue/inputtext';
import Button from "primevue/button"
import { useRouter } from "vue-router";
import { onMounted, ref, watch } from 'vue';
import { getProducts } from '@/api/api.js';

const router = useRouter();
const products = ref({
    data: []
});
const searchKey = ref('');
const BASE_URL = import.meta.env.VITE_APP_API_URL;

onMounted(() => {
    handleGetProducts();
});

watch(searchKey, (newValue) => {
    if (newValue) {
        let data = {
            params: `?search=${newValue}`
        }
        handleGetProducts(data);

    }
});

const handleGetProducts = async (data) => {
    try {
        const response = await getProducts(data??{});
        console.log("Response:", response);
        products.value = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    }
};

</script>

<style  scoped>

</style>