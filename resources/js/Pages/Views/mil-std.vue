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


</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="fw-bold mb-0 text-dark">
                    Selección de Producto para Muestreo
                </h2>

            </div>
        </template>

        <div class="container mt-4 pb-5">
            <div v-if="loading" class="text-center p-5 bg-white rounded-4 shadow-sm border">
                <div class="spinner-border text-emerald mb-3" role="status"></div>
                <p class="text-muted fw-bold">Cargando productos de la orden...</p>
            </div>

            <div v-else-if="purchaseOrder" class="main-scroll">
                
                <div class="card shadow-sm mb-4 compact-card border-top border-4 border-emerald">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <span class="info-label-small">Folio de Referencia</span>
                                <h5 class="fw-bold text-dark mb-0">{{ purchaseOrder.folio }}</h5>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="info-label-small">Fecha de Emisión</span>
                                <span class="fw-bold text-muted">{{ purchaseOrder.date }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-secondary text-uppercase small" style="letter-spacing: 1px;">
                    Productos disponibles para inspeccionar
                </h6>

                <div class="row g-3">
                    <div 
                        v-for="product in purchaseOrder.products" 
                        :key="product.id_product"
                        class="col-md-6 col-xl-4"
                    >
                        <div class="card h-100 shadow-sm compact-card border-0 hover-card">
                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex justify-content-center align-items-start mb-3">
                                    <div class="bg-lightgray p-2 rounded-3 border">
                                        <span class="info-label-small">SKU: {{ product.code }}</span>
                                        <h6 class="fw-bold text-dark mb-0">{{ product.title }}</h6>
                                        
                                    </div>
                                </div>

                                <div v-if="getDetail(product.id_product)" class="bg-light-emerald border border-emerald p-3 rounded-3 mb-4">
                                    <div class="row text-center">
                                        <div class="col-6 border-end">
                                            <span class="info-label-small text-emerald">Lote</span>
                                            <span class="fw-bold text-dark">{{ getDetail(product.id_product).lot }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="info-label-small text-emerald">Cantidad</span>
                                            <span class="fw-bold text-dark">
                                                {{ getDetail(product.id_product).bulk_or_roll_quantity || '0' }} 
                                                <small class="text-muted">{{ getDetail(product.id_product).unit_measure }}</small>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    @click="startInspection(product.id_product)"
                                    class="btn btn-success mt-auto w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 shadow-sm transition"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                                    </svg>
                                    INICIAR INSPECCIÓN
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="alert alert-danger border-0 shadow-sm rounded-4 p-4 text-center">
                <i class="bi bi-exclamation-triangle-fill fs-2 d-block mb-2"></i>
                <h5 class="fw-bold">Error de sincronización</h5>
                <p class="mb-0">No se pudo recuperar la información de la orden de compra seleccionada.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estructura Principal */
.main-scroll {
  height: calc(100vh - 180px);
  overflow-y: auto;
  padding-right: 8px;
}
.main-scroll::-webkit-scrollbar { width: 6px; }
.main-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

/* Cards y Colores */
.compact-card {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: white;
  transition: all 0.25s ease;
}
.hover-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
  border-color: #009975;
}

.bg-lightgray { background-color: #f8fafc; }
.text-emerald { color: #009975; }
.bg-emerald-light { background-color: #ecfdf5; }
.bg-light-emerald { background-color: #f0fdf4; }

/* Tipografía y labels */
.info-label-small {
  font-size: 10px;
  text-transform: uppercase;
  color: #64748b;
  display: block;
  margin-bottom: 2px;
  letter-spacing: 0.5px;
}

/* Botones */
.btn-success { 
    background-color: #009975; 
    border-color: #009975; 
}
.btn-success:hover { 
    background-color: #007d60; 
    border-color: #007d60;
}
</style>