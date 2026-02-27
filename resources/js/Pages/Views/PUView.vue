<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

// ================== PROPS DESDE INERTIA ==================
const page = usePage()

// Lista de órdenes (seguro aunque venga vacío)
const orders = computed(() => page.props.orders?.data ?? [])

// ================== FILTRO LOCAL ==================
const searchFolio = ref('')
const searchStatus = ref('')
const searchDate = ref('')

const filteredOrders = computed(() => {

  return orders.value.filter(order => {

    /* ===== FOLIO ===== */
    const matchFolio =
      !searchFolio.value ||
      order.folio
        ?.toLowerCase()
        .includes(searchFolio.value.toLowerCase())

    /* ===== STATUS ===== */
    const matchStatus =
      !searchStatus.value ||
      order.status === searchStatus.value

    /* ===== FECHA ===== */
    const matchDate =
      !searchDate.value ||
      order.date?.slice(0,10) === searchDate.value

    return matchFolio && matchStatus && matchDate
  })

})

const getStatusClass = (status) => {
    if (!status) return 'bg-secondary'

    const normalized = status.toUpperCase().trim()

    const item = statusOptions.value.find(
        s => s.value === normalized
    )

    return item?.class || 'bg-secondary'
}
// En tu <script setup> o data
const statusOptions = ref([
    { label: 'BORRADOR', value: 'PENDIENTE', class: 'bg-secondary' },
    { label: 'CHECK BPM COMPLETO', value: 'PENDIENTE1', class: 'bg-warning text-dark' },
    { label: 'COMPLETADA', value: 'COMPLETADA', class: 'bg-success' },
    { label: 'CANCELADO', value: 'CANCELADA', class: 'bg-danger' },
])

const getStatusLabel = (status) => {
    if (!status) return '---'

    const normalized = status.toUpperCase().trim()

    const item = statusOptions.value.find(
        s => s.value === normalized
    )

    return item?.label || normalized
}


// ================== ELIMINAR ==================
const deleteOrder = (id) => {
    if (confirm('¿Seguro que deseas eliminar esta orden?')) {
        router.delete(`/purchase-order/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Eliminado y lista refrescada');
            }
        })
    }
}

const goToOrder = (order) => {

    const status = order.status?.toUpperCase().trim()

    switch (status) {

        case 'PENDIENTE':
            router.visit(route('purchase-order.test', {
                purchase_order: order.id_purchase_order
            }))
            break

        case 'PENDIENTE1':
            router.visit(`/mil-std/${order.id_purchase_order}`)
            break

        case 'COMPLETADA':
            router.visit(route('purchase-order.show', {
                purchase_order: order.id_purchase_order
            }))
            break

        default:
            router.visit(`/purchase-order/${order.id_purchase_order}`)
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
                <div class="card-header bg-emerald text-white">
                <div class="row g-2 align-items-center">

                    <div class="col">
                    <h5 class="mb-0 fw-bold text-white">
                        Listado de Compras
                    </h5>
                    </div>

                    <!-- FILTROS -->
                    <div class="col-auto d-flex gap-2">

                    <!-- FOLIO -->
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                        <i class="fa-solid fa-hashtag"></i>
                        </span>
                        <input
                        v-model="searchFolio"
                        type="text"
                        class="form-control shadow-none focus-ring-0"
                        placeholder="Folio"
                        >
                    </div>

                    <!-- FECHA -->
                    <input
                        v-model="searchDate"
                        type="date"
                        class="form-control form-control-sm shadow-none focus-ring-0"
                    >

                    <select v-model="searchStatus" class="form-select form-select-sm border-0 shadow-none">
                        <option value="">Estado</option>

                        <option
                            v-for="item in statusOptions"
                            :key="item.value"
                            :value="item.value"
                        >
                            {{ item.label }}
                        </option>
                    </select>

                    </div>

                </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="py-3 ps-4">Proveedor</th>
                                    <th class="py-3 text-center">Folio</th>
                                    <th class="py-3 text-center">Fecha de Emisión</th>
                                    <th class="py-3 text-center">Estado</th>
                                    <th class="py-3 text-end pe-4" width="220">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="border-top-0">
                                <tr v-if="filteredOrders.length === 0">
                                    <td colspan="5" class="py-5 text-center text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox fs-1 mb-2 opacity-25"></i>
                                            <p class="mb-0">No se encontraron órdenes registradas.</p>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-for="order in filteredOrders" :key="order.id_purchase_order">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ order.provider?.name || 'S/N' }}</div>
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
                                            :class="getStatusClass(order.status)"
                                        >
                                            <i class="bi bi-dot me-1"></i>
                                            {{ getStatusLabel(order.status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-3">
                                            <button
                                                v-tooltip="`Continuar`"
                                                class="btn btn-white btn-sm border fw-bold text-primary px-3"
                                                @click="goToOrder(order)"
                                            >
                                                <i class="fa-solid fa-forward fa-beat fa-sm" style="color: rgb(48, 70, 109);"></i>
                                            </button>
                                            <button
                                                v-tooltip="`Editar OC`"
                                                class="btn btn-white btn-sm border fw-bold text-success px-3"
                                                @click="router.visit(`/purchase-order/${order.id_purchase_order}`)"
                                            >
                                                <!-- <i class="fa-duotone fa-solid fa-file-pen"></i> -->
                                                <i class="fa-duotone fa-solid fa-file-pen" style="--fa-primary-color: rgb(121, 164, 112); --fa-secondary-color: rgb(121, 164, 112);"></i>
                                            </button>
                                            <button
                                                v-tooltip="`Eliminar OC`"
                                                class="btn btn-white btn-sm border text-danger px-3"
                                                @click="deleteOrder(order.id_purchase_order)"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-emerald-light">
                    <div class="d-flex justify-content-between align-items-center small text-muted">
                        <span>Mostrando {{ orders.length }} resultados</span>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled"><a class="page-link" href="#">Anterior</a></li>
                                <li class="page-item active"><a class="page-link text-white bg-emerald border-color-emerald" href="#">1</a></li>
                                <li class="page-item"><a class="page-link text-emerald" href="#">Siguiente</a></li>
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