<script setup>
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, onMounted, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'

// ================== FECHA LOCAL ==================
const getTodayLocalDate = () => {
  const today = new Date()
  const offset = today.getTimezoneOffset()
  today.setMinutes(today.getMinutes() - offset)
  return today.toISOString().split('T')[0]
}
const goDashboard = () => {
  window.location.href = '/dashboard'
}

// ================== DATOS ==================
const plates = ref([])
const providers = ref([])
const products = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pagination = ref({ total: 0, per_page: 10, current_page: 1, last_page: 1 })

const filters = ref({ search: '' })

const unitMeasures = [
  { value: 'mil', label: 'Millares' },
  { value: 'kg', label: 'Kilogramos' },
  { value: 'pz', label: 'Piezas' },
]

const documentTypes = [
  { value: 'FACTURA', label: 'FACTURA' },
  { value: 'REMISION', label: 'REMISION' },
  { value: 'OTRO', label: 'OTRO' },
]

const form = ref({
  date: getTodayLocalDate(),
  status: 'PENDIENTE',
  id_provider: '',
  state: '',
  plates: [],
  products: [],
  document_number: '',
  document_type: '',
  global_document_number: '',
  use_global_document: false,
})

// ================== PLACAS ==================
const selectedPlate = ref('')
const newPlate = ref('')

const isAddingNewPlate = computed(() => selectedPlate.value === '__new__')

watch(selectedPlate, (val) => {
  if (val && val !== '__new__') {
    if (!form.value.plates.includes(val)) form.value.plates.push(val)
    newPlate.value = ''
    selectedPlate.value = '' // resetea el select después de agregar
  } else if (val === '__new__') {
    newPlate.value = ''
  }
})

const addPlate = () => {
  if (!newPlate.value) return
  const formatted = newPlate.value.trim().toUpperCase()
  if (!form.value.plates.includes(formatted)) {
    form.value.plates.push(formatted)
  }
  newPlate.value = ''
  selectedPlate.value = ''
}

const removePlate = (index) => {
  form.value.plates.splice(index, 1)
}

// ================== PRODUCTOS ==================
const fetchProducts = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams({
      search: filters.value.search,
      state: filters.value.state,
      page: currentPage.value
    }).toString()

    const response = await fetch(`http://localhost:8000/api/products?${params}`)
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
    if (!newProviderId) {
      plates.value = []
      form.value.plates = []
      form.value.state = ''
      return
    }
    const selected = providers.value.find(p => p.id_provider == newProviderId)
    plates.value = selected?.plates?.map(p => p.plate_number) ?? []
    form.value.state = selected?.state ?? ''
    form.value.plates = []
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
    document_number: form.value.use_global_document ? form.value.document_number : '',
    document_type: form.value.use_global_document ? form.value.document_type : '',
    non_conformity: false,
    lot: '',
  })
}

const removeProduct = (uid) => {
  form.value.products = form.value.products.filter(p => p.uid !== uid)
}

// ================== GUARDAR ORDEN ==================
const saveOrder = async () => {
  const response = await fetch(`http://localhost:8000/api/purchase-order`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(form.value),
  })

  if (!response.ok) {
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
    console.log("RESPUESTA COMPLETA:", data)

    const id = data.data.id_purchase_order

    window.location.href = `/purchase-order/${id}/test`

  } catch (error) {
    alert('No se pudo guardar la orden')
  }
}

// ================== VALIDACIÓN ==================
const OrderComplete = computed(() => {
  if (!form.value.id_provider) return false
  if (form.value.plates.length === 0) return false
  if (form.value.products.length === 0) return false

  if (form.value.use_global_document) {
    if (!form.value.document_type || !form.value.document_number) return false
  }

  for (const item of form.value.products) {
    if (!item.unit_measure || item.bulk_or_roll_quantity <= 0) return false
    if (!form.value.use_global_document && (!item.document_type || !item.number)) return false
  }

  return true
})
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="fw-bold mb-0">Orden de Pedido</h2>
    </template>

    <div class="container-fluid py-3 bg-lightgray rounded-3 main-scroll">

      <div class="card shadow-sm mb-3 p-3 rounded-3">
        <div class="row g-3 align-items-center">

          <div class="col-md-2">
            <label class="form-label fw-semibold small">Fecha</label>
            <input type="text" class="form-control form-control-sm bg-light" :value="form.date" disabled>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold small">Proveedor</label>
            <select
              class="form-select form-select-sm"
              v-model="form.id_provider"
              :class="{'border-success': form.id_provider, 'border-danger': !form.id_provider}"
            >
              <option value="">Seleccionar proveedor</option>
              <option v-for="p in providers" :key="p.id_provider" :value="p.id_provider">{{ p.name }}</option>
            </select>
            <small v-if="!form.id_provider" class="text-danger">Campo obligatorio</small>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold small">Estado</label>
            <input type="text" class="form-control form-control-sm bg-light" :value="form.state || 'N/A'" disabled>
          </div>

          <div class="col-md-5">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
              <label class="form-label fw-semibold small me-2">Placas:</label>

              <span v-for="(plate, index) in form.plates" :key="index"
                    class="badge rounded-pill bg-success">
                {{ plate }}
                <button type="button" class="btn-close btn-close-white btn-sm ms-1" @click="removePlate(index)"></button>
              </span>
            </div>

            <div class="d-flex gap-2 mb-2">
              <select
                class="form-select form-select-sm flex-grow-1"
                :class="{
                  'border-success': form.plates.length > 0,
                  'border-danger': form.plates.length === 0 && selectedPlate !== ''
                }"
                :disabled="!form.id_provider"
                v-model="selectedPlate"
              >
                <option value="">Seleccionar placa</option>
                <option v-for="plate in plates" :key="plate" :value="plate">{{ plate }}</option>
                <option value="__new__">Otra...</option>
              </select>

              <input 
                type="text"
                v-model="newPlate"
                placeholder="Nueva placa"
                :class="{
                  'border-success': form.plates.length > 0,
                  'border-danger': isAddingNewPlate && !newPlate
                }"
                :disabled="!isAddingNewPlate"
                @keyup.enter="addPlate"
              />

              <button 
                class="btn btn-sm btn-primary" 
                @click="addPlate" 
                :disabled="!newPlate || !isAddingNewPlate"
              >+</button>
            </div>

            <small v-if="form.plates.length === 0 && selectedPlate !== '__new__'" class="text-danger">
              No hay placas agregadas
            </small>
          </div>
        </div>

        <div class="row g-3 align-items-center mt-3">
          <div class="col-md-6 d-flex align-items-center">
            <input type="checkbox" class="form-check-input me-2" v-model="form.use_global_document">
            <label class="small fw-semibold mb-0">Usar mismo documento para todos los productos</label>
          </div>

          <div v-if="form.use_global_document" class="col-md-3">
            <label class="form-label fw-semibold small">Tipo Doc</label>
            <select
              class="form-select form-select-sm"
              v-model="form.document_type"
              :class="{'border-success': form.document_type, 'border-danger': !form.document_type}"
            >
              <option value="">Seleccione</option>
              <option v-for="d in documentTypes" :key="d.value" :value="d.value">{{ d.label }}</option>
            </select>
            <small v-if="!form.document_type" class="text-danger">Campo obligatorio</small>
          </div>

          <div v-if="form.use_global_document" class="col-md-3">
            <label class="form-label fw-semibold small">Número Doc</label>
            <input type="text"
                  class="form-control form-control-sm"
                  v-model="form.document_number"
                  placeholder="Ej: 1234"
                  :class="{'border-success': form.document_number, 'border-danger': !form.document_number}"
            >
            <small v-if="!form.document_number" class="text-danger">Campo obligatorio</small>
          </div>
        </div>

      </div>

      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body py-2">
          <label class="form-label small fw-bold">Buscar producto</label>
          <input type="text"
                 class="form-control form-control-sm"
                 placeholder="Escribe para buscar..."
                 v-model="filters.search">
        </div>
      </div>

      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body p-0">
          <table class="table table-sm table-hover mb-0 text-center align-middle small">
            <thead class="table-light">
              <tr>
                <th>Título</th>
                <th>Clave</th>
                <th>Descripción</th>
                <th>Agregar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id_product">
                <td>{{ product.title }}</td>
                <td>{{ product.code }}</td>
                <td class="text-truncate">{{ product.description }}</td>
                <td>
                  <button class="btn btn-sm btn-success" @click="addProduct(product)">+</button>
                </td>
              </tr>
              <tr v-if="products.length === 0">
                <td colspan="4" class="text-muted py-2">No hay productos</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body p-0">
          <table class="table table-sm table-bordered mb-0 text-center align-middle small">
            <thead class="table-success small fw-bold">
              <tr>
                <th>Clave</th>
                <th>Descripción</th>
                <th>Unidad</th>
                <th>Bultos</th>
                <th>Pzas</th>
                <th>Orden</th>
                <th>NC</th>
                <th>Lote</th>
                <th v-if="!form.use_global_document">Tipo Doc</th>
                <th v-if="!form.use_global_document">No Doc</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in form.products" :key="item.uid">
                <td>{{ item.code }}</td>
                <td class="text-truncate">{{ item.description }}</td>

                <!-- Unidad -->
                <td>
                  <select
                    class="form-select form-select-sm"
                    v-model="item.unit_measure"
                    :class="{
                      'border-success': item.unit_measure,
                      'border-danger': !item.unit_measure
                    }"
                  >
                    <option value="">Seleccione</option>
                    <option v-for="u in unitMeasures" :key="u.value" :value="u.value">{{ u.label }}</option>
                  </select>
                </td>

                <!-- Bultos -->
                <td>
                  <input
                    type="number"
                    class="form-control form-control-sm"
                    v-model="item.bulk_or_roll_quantity"
                    :class="{
                      'border-success': item.bulk_or_roll_quantity > 0,
                      'border-danger': item.bulk_or_roll_quantity <= 0
                    }"
                  />
                </td>

                <!-- Pzas -->
                <td>
                  <input
                    type="number"
                    class="form-control form-control-sm"
                    v-model="item.individual_quantity"
                    :class="{
                      'border-success': item.individual_quantity > 0,
                      'border-danger': item.individual_quantity <= 0
                    }"
                  />
                </td>

                <!-- Orden -->
                <td>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    v-model="item.document_number"
                    placeholder="Ej: OC-1234"
                    :class="{
                      'border-success': item.document_number,
                      'border-danger': !item.document_number
                    }"
                  />
                </td>

                <!-- NC -->
                <td>
                  <input type="checkbox" class="form-check-input" v-model="item.non_conformity" />
                </td>

                <!-- Lote -->
                <td>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    v-model="item.lot"
                    placeholder="Ej: LOTE-5678"
                    :class="{
                      'border-success': item.lot,
                      'border-danger': !item.lot
                    }"
                  />
                </td>

                <!-- Tipo Doc -->
                <td v-if="!form.use_global_document">
                  <select
                    v-model="item.document_type"
                    class="form-select form-select-sm"
                    :class="{
                      'border-success': item.document_type,
                      'border-danger': !item.document_type
                    }"
                  >
                    <option value="">Seleccione</option>
                    <option v-for="d in documentTypes" :key="d.value" :value="d.value">{{ d.label }}</option>
                  </select>
                </td>

                <!-- No Doc -->
                <td v-if="!form.use_global_document">
                  <input
                    type="text"
                    v-model="item.document_number"
                    class="form-control form-control-sm"
                    placeholder="Ej: DOC-9012"
                    :class="{
                      'border-success': item.document_number,
                      'border-danger': !item.document_number
                    }"
                  />
                </td>

                <!-- Botón eliminar -->
                <td>
                  <button class="btn btn-sm btn-outline-danger" @click="removeProduct(item.uid)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </td>
              </tr>

              <tr v-if="form.products.length === 0">
                <td colspan="11" class="text-muted py-2">No hay productos agregados</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-outline-secondary btn-sm" @click="goDashboard">Cancelar</button>
        <button v-if="OrderComplete" class="btn btn-success btn-sm" @click="goTest">Guardar orden</button>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.main-scroll {
  height: calc(100vh - 120px);
  overflow-y: auto;
  padding-right: 4px;
}
.main-scroll::-webkit-scrollbar {
  width: 6px;
}
.main-scroll::-webkit-scrollbar-thumb {
  background-color: #2d2b298a;
  border-radius: 10px;
}
.main-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.compact-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: 0.2s ease-in-out;
  background: white;
}
.compact-card:hover {
  box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

.table td, .table th {
  padding: 6px !important;
  font-size: 12px;
  vertical-align: middle !important;
}
.table thead {
  font-size: 12px;
  letter-spacing: 0.3px;
}

.btn-success {
  background-color: #009975 !important;
  border-color: #009975 !important;
  border-radius: 6px;
}
.btn-success:hover {
  background-color: #15803d !important;
  border-color: #15803d !important;
}
.btn-outline-danger {
  border-radius: 6px;
}

h2 {
  font-size: 18px;
  letter-spacing: 0.5px;
}
.bg-lightgray {
  background-color: #f3f4f6;
}

.is-valid {
  border-color: #22c55e !important;
  background-color: #ecfdf5 !important;
}

.is-invalid {
  border-color: #dc2626 !important;
  background-color: #fee2e2 !important;
}
</style>

