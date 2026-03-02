<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { onMounted, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage<any>()
const id_purchase_order = page.props.id_purchase_order
const purchaseOrder = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
    try {
        const response = await axios.get(`/mil-std/api/${id_purchase_order}/products`)
        purchaseOrder.value = response.data.data
    } catch (error) {
        console.error("Error al cargar la orden:", error)
    } finally {
        loading.value = false
    }
})

const getDetail = (id_product: number) => {
    return purchaseOrder.value?.orderDetails?.find(
        (d: any) => Number(d.id_product) === id_product
    )
}

const startInspection = (id_product: number) => {
    router.visit(`/localM/${id_purchase_order}/${id_product}/inspection`)
}
const getInspection = (id_product: number) => {
    return purchaseOrder.value?.products?.find(
        (p: any) => Number(p.id_product) === id_product
    )
}

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Selección de Producto para Muestreo
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="loading" class="text-center p-6 bg-white rounded-lg shadow">
                    <p class="animate-pulse text-gray-500">Cargando productos de la orden...</p>
                </div>

                <div v-else-if="purchaseOrder" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-lg font-bold text-blue-600">
                            Orden de compra: {{ purchaseOrder.folio }}
                        </h3>
                        <p class="text-sm text-gray-500">Fecha: {{ purchaseOrder.date }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div 
                            v-for="product in purchaseOrder.products" 
                            :key="product.id_product"
                            class="border border-gray-200 p-4 rounded-xl hover:bg-gray-50 transition shadow-sm flex flex-col justify-between"
                        >
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-gray-800 text-lg">{{ product.title }}</h4>
                                    <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">
                                        ID: {{ product.id_product }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-1">
                                    <span class="font-semibold text-gray-700">Código:</span> {{ product.code }}
                                </p>

                                <div v-if="getDetail(product.id_product)" class="bg-blue-50 p-2 rounded mt-2">
                                    <p class="text-xs text-blue-700">
                                        <strong>Lote:</strong> {{ getDetail(product.id_product).lot }}
                                    </p>
                                    <p class="text-xs text-blue-700">
                                        <strong>Cantidad:</strong> {{ getDetail(product.id_product).bulk_or_roll_quantity || 'N/A' }} 
                                        {{ getDetail(product.id_product).unit_measure }}
                                    </p>
                                </div>
                            </div>

                            <button
                                @click="startInspection(product.id_product)"
                                class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 flex justify-center items-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                </svg>
                                Iniciar inspección
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center p-6 bg-red-50 rounded-lg border border-red-200 text-red-600">
                    No se encontró información de la orden.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>