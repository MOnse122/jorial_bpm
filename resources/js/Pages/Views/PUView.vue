<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

// ================== PROPS DESDE INERTIA ==================
const page = usePage()

// Lista de órdenes (seguro aunque venga vacío)
const orders = computed(() => page.props.orders?.data ?? [])

// ================== FILTRO LOCAL ==================
const search = ref('')

const filteredOrders = computed(() => {
    if (!search.value) return orders.value

    return orders.value.filter(order =>
        order.id_purchase_order?.toString().includes(search.value) ||
        order.provider?.name?.toLowerCase().includes(search.value.toLowerCase())
        
    )

})



// ================== NAVEGACIÓN ==================
const goToOrder = (id) => {
    router.visit(`/purchase-order/${id}`)
}

// ================== ELIMINAR ==================
const deleteOrder = (id) => {
    console.log(id)
    if (confirm('¿Seguro que deseas eliminar esta orden?')) {
        router.delete(`/purchase-order/${id}`, {
            preserveScroll: true,
        })
    } try {
        router.visit('/purchase-order', {
            preserveScroll: true,
        })
    } catch (error) {
        console.error('Error al eliminar la orden:', error)
    }
}
</script>
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center w-100">
                <h2 class="fw-bold mb-0 text-dark">Órdenes de Pedido</h2>

            </div>
        </template>

        <div class="container-fluid py-4 bg-lightgray main-scroll">
            
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0 fw-bold text-secondary">Listado de Compras</h5>
                        </div>
                        <div class="col-auto">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" placeholder="Filtrar orden...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="py-3">Proveedor</th>
                                    <th class="py-3 text-center">Folio</th>
                                    <th class="py-3 text-center">Fecha de Emisión</th>
                                    <th class="py-3 text-center">Estado</th>
                                    <th class="py-3 text-end pe-4" width="220">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="border-top-0">
                                <tr v-if="orders.length === 0">
                                    <td colspan="5" class="py-5 text-center text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox fs-1 mb-2 opacity-25"></i>
                                            <p class="mb-0">No se encontraron órdenes registradas en el sistema.</p>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-for="order in filteredOrders" :key="order.id_purchase_order" class="order-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3 bg-emerald-light text-emerald rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                                {{ order.provider?.name?.charAt(0) ?? 'S' }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ order.provider?.name ?? 'Sin proveedor' }}</div>
                                                <div class="small text-muted text-uppercase" style="font-size: 10px;">Proveedor Verificado</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="small fw-medium text-secondary">
                                            <i class="bi bi-file-text me-1"></i> {{ order.folio }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="small fw-medium text-secondary">
                                            <i class="bi bi-calendar3 me-1"></i> {{ order.date }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span 
                                            class="badge rounded-pill px-3 py-2 fw-bold"
                                            :class="{
                                                'bg-success-subtle text-success border border-success': order.status === 'APROBADO',
                                                'bg-warning-subtle text-warning-emphasis border border-warning': order.status === 'PENDIENTE',
                                                'bg-danger-subtle text-danger border border-danger': order.status === 'RECHAZADO'
                                            }"
                                        >
                                            <i class="bi bi-dot"></i> {{ order.status }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3">
                                            <button
                                                v-tooltip="`Continuar`"
                                                class="btn btn-white btn-sm border fw-bold text-primary px-3"
                                                @click="router.visit(route('purchase-order.test', { purchase_order: order.id_purchase_order }))"
                                            >
                                                <i class="fa-duotone fa-solid fa-file-pen"></i>
                                            </button>
                                            <button
                                                v-tooltip="`Editar OC`"
                                                class="btn btn-white btn-sm border fw-bold text-primary px-3"
                                                @click="router.visit(`/purchase-order/${order.id_purchase_order}`)"
                                            >
                                                <i class="fa-sharp-duotone fa-solid fa-folder-open"></i>
                                            </button>
                                            <button
                                                v-tooltip="`Eliminar OC`"
                                                class="btn btn-white btn-sm border text-danger px-3"
                                                @click="deleteOrder(order.id_purchase_order)"
                                            >
                                                <i class="fa-solid fa-trash""></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white py-3 border-top border-light">
                    <div class="d-flex justify-content-between align-items-center small text-muted">
                        <span>Mostrando {{ orders.length }} resultados</span>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled"><a class="page-link" href="#">Anterior</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">Siguiente</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estructura y Scrollbar idénticos a la vista anterior para consistencia */
.main-scroll {
  height: calc(100vh - 120px);
  overflow-y: auto;
  padding-right: 4px;
}
.main-scroll::-webkit-scrollbar { width: 6px; }
.main-scroll::-webkit-scrollbar-thumb { background-color: #2d2b298a; border-radius: 10px; }

/* Colores de Marca */
.bg-lightgray { background-color: #f3f4f6; }
.bg-emerald { background-color: #009975 !important; }
.text-emerald { color: #009975 !important; }
.bg-emerald-light { background-color: #ecfdf5; }

/* Avatar circular pequeño para el proveedor */
.avatar-sm {
    width: 35px;
    height: 35px;
    font-size: 14px;
}

/* Efecto en filas */
.order-row {
    transition: all 0.2s ease;
}
.order-row:hover {
    background-color: #f9fafb !important;
}

/* Botones estilo "Clean" */
.btn-white {
    background-color: #fff;
    border-color: #e5e7eb;
}
.btn-white:hover {
    background-color: #f9fafb;
    border-color: #d1d5db;
}

/* Tipografía */
h2 {
  font-size: 20px;
  letter-spacing: -0.025em;
}
.table thead th {
    font-size: 11px;
    color: #6b7280;
    letter-spacing: 0.05em;
}
</style>