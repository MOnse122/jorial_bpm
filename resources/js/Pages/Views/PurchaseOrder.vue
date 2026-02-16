<script setup>
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'

/* ============================= */
/* FUNCION FECHA LOCAL */
/* ============================= */
const getTodayLocalDate = () => {
  const today = new Date()
  const offset = today.getTimezoneOffset()
  today.setMinutes(today.getMinutes() - offset)
  return today.toISOString().split('T')[0]
}
const goDashboard = () => {
  window.location.href = '/dashboard'
  console.log('Dashboard clicked')
  console.log('Redirigiendo a /dashboard')
}

const providers = ref([])
const products = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pagination = ref({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
})


const filters = ref({
  search: ''
})

const unitMeasures = [
  { value: 'mil', label: 'Millares' },
  { value: 'kg', label: 'Kilogramos' },
  { value: 'pz', label: 'Piezas' },
]

const documentTypes = [
  { value: 'FACTURA', label: 'Factura' },
  { value: 'REMISION', label: 'Remisión' },
  { value: 'OTRO', label: 'Otro' },
]


const form = ref({
  date: getTodayLocalDate(),
  status: 'PENDIENTE',
  id_provider: '',
  state: '',
  products: []
})

/* ============================= */

const fetchProducts = async () => {
  try {
    loading.value = true

    const params = new URLSearchParams({
      search: filters.value.search,
      state: filters.value.state,
      page: currentPage.value
    }).toString()

    const response = await fetch(
      `http://localhost:8000/api/products?${params}`
    )

    const data = await response.json()

    products.value = data.data
    pagination.value = data

  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

watch(filters, fetchProducts, { deep: true })

const fetchProviders = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/providers')
    providers.value = await response.json()
  } catch (err) {
    console.error(err)
  }
}

watch(
  () => form.value.id_provider,
  (newProviderId) => {
    const selected = providers.value.find(
      p => p.id_provider == newProviderId
    )
    form.value.state = selected ? selected.state : ''
  }
)
const addProduct = (product) => {
  form.value.products.push({
    uid: Date.now() + Math.random(), 
    id_product: product.id_product,
    code: product.code,
    description: product.description,
    unit_measure: '',
    bulk_or_roll_quantity: 0,
    individual_quantity: 0,
    document_number: '',
    non_conformity: false,
    lot: '',
    document_type: '',
    number: '',
  })
}




const removeProduct = (uid) => {
  form.value.products = form.value.products.filter(
    p => p.uid !== uid
  )
}


const saveOrder = async () => {
  const response = await fetch(
    'http://localhost:8000/api/purchase-order',
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value),
    }
  )

  if (!response.ok) {
    const errorData = await response.json()
    console.error(errorData)
    throw new Error('Error al guardar la orden')
  }

  return await response.json()
}



onMounted(() => {
  fetchProducts()
  fetchProviders()
})

const goTest = async () => {
  try {
    const data = await saveOrder()
    console.log("Orden guardada correctamente:", data)
    router.visit(route('test'))
  } catch (error) {
    alert('No se pudo guardar la orden')
  }
}




</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="fw-bold mb-0">Orden de Pedido</h2>
    </template>

    <div class="container-fluid py-3">

      <!-- ================= INFO GENERAL ================= -->
      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body py-2">
          <div class="row g-2 align-items-end">

            <div class="col-md-2">
              <label class="form-label small fw-bold">Fecha</label>
              <input class="form-control form-control-sm bg-light" 
                     :value="form.date" disabled>
            </div>

            <div class="col-md-2">
              <label class="form-label small fw-bold">Estatus</label>
              <input class="form-control form-control-sm bg-light" 
                     :value="form.status" disabled>
            </div>

            <div class="col-md-3">
              <label class="form-label small fw-bold">Proveedor</label>
              <select class="form-select form-select-sm"
                      v-model="form.id_provider">
                <option value="">Seleccionar</option>
                <option v-for="p in providers"
                        :key="p.id_provider"
                        :value="p.id_provider">
                  {{ p.name }}
                </option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label small fw-bold">Estado</label>
              <input class="form-control form-control-sm bg-light"
                     :value="form.state || 'N/A'"
                     disabled>
            </div>

          </div>
        </div>
      </div>

      <!-- ================= BUSCADOR ================= -->
      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body py-2">
          <label class="form-label small fw-bold">Buscar producto</label>
          <input type="text"
                 class="form-control form-control-sm"
                 placeholder="Escribe para buscar..."
                 v-model="filters.search">
        </div>
      </div>

      <!-- ================= RESULTADOS ================= -->
      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body p-0">
          <table class="table table-sm table-hover mb-0 text-center align-middle small">
            <thead class="table-light">
              <tr>
                <th width="20%">Título</th>
                <th width="15%">Clave</th>
                <th width="45%">Descripción</th>
                <th width="10%">Agregar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id_product">
                <td>{{ product.title }}</td>
                <td>{{ product.code }}</td>
                <td class="text-truncate">{{ product.description }}</td>
                <td>
                  <button class="btn btn-sm btn-success"
                          @click="addProduct(product)">
                    +
                  </button>
                </td>
              </tr>

              <tr v-if="products.length === 0">
                <td colspan="4" class="text-muted py-2">
                  No hay productos
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ================= PRODUCTOS AGREGADOS ================= -->
      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body p-0">

          <table class="table table-sm table-bordered mb-0 text-center align-middle small">
            <thead style="background:#2e7d32;" class="text-white">
              <tr>
                <th width="10%">Clave</th>
                <th width="15%">Descripción</th>
                <th width="10%">Unidad</th>
                <th width="6%">Bultos</th>
                <th width="6%">Pzas</th>
                <th width="10%">Orden</th>
                <th width="6%">NC</th>
                <th width="8%">Lote</th>
                <th width="10%">Tipo Doc</th>
                <th width="10%">No Doc</th>
                <th width="4%"></th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="item in form.products" :key="item.uid">
                <td>{{ item.code }}</td>
                <td class="text-truncate">{{ item.description }}</td>

                <td>
                  <select class="form-select form-select-sm compact-input"
                          v-model="item.unit_measure">
                    <option value="">-</option>
                    <option v-for="u in unitMeasures"
                            :key="u.value"
                            :value="u.value">
                      {{ u.label }}
                    </option>
                  </select>
                </td>

                <td>
                  <input type="number"
                         class="form-control form-control-sm compact-input"
                         v-model="item.bulk_or_roll_quantity">
                </td>

                <td>
                  <input type="number"
                         class="form-control form-control-sm compact-input"
                         v-model="item.individual_quantity">
                </td>

                <td>
                  <input type="text"
                         class="form-control form-control-sm compact-input"
                         v-model="item.document_number">
                </td>

                <td>
                  <input type="checkbox"
                         class="form-check-input"
                         v-model="item.non_conformity">
                </td>

                <td>
                  <input type="text"
                         class="form-control form-control-sm compact-input"
                         v-model="item.lot">
                </td>

                <td>
                  <select class="form-select form-select-sm compact-input"
                          v-model="item.document_type">
                    <option value="">-</option>
                    <option v-for="d in documentTypes"
                            :key="d.value"
                            :value="d.value">
                      {{ d.label }}
                    </option>
                  </select>
                </td>

                <td>
                  <input type="text"
                         class="form-control form-control-sm compact-input"
                         v-model="item.number">
                </td>

                <td>
                  <button class="btn btn-sm btn-outline-danger" @click="removeProduct(item.uid)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </td>
              </tr>

              <tr v-if="form.products.length === 0">
                <td colspan="11" class="text-muted py-2">
                  No hay productos agregados
                </td>
              </tr>
            </tbody>

          </table>
        </div>
      </div>
      <!-- ================= BOTONES ================= -->
      <div class="d-flex justify-content-end gap-2">
        <button 
          class="btn btn-outline-secondary btn-sm"
          @click="goDashboard"
        >
          Cancelar
        </button>

        <button 
          class="btn btn-success btn-sm"
          @click="goTest"
        >
          Guardar orden
        </button>
      </div>

    </div>
  </AppLayout>
</template>
<style scoped>
.compact-card {
  border-radius: 8px;
}

.compact-input {
  padding: 2px 4px !important;
  font-size: 12px;
}

.table td,
.table th {
  padding: 6px !important;
  font-size: 12px;
}

h2 {
  font-size: 20px;
}
</style>

