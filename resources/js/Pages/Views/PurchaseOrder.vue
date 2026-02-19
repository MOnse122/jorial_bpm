<script setup>
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, onMounted, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'


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

const plates = ref([])
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
  { value: 'FACTURA', label: 'FACTURA' },
  { value: 'REMISION', label: 'REMISION' },
  { value: 'OTRO', label: 'OTRO' },
]

const form = ref({
  date: getTodayLocalDate(),
  status: 'PENDIENTE',
  id_provider: '',
  state: '',
  plates : [],
  products: [],
  document_number: '',
  document_type: '',
  global_document_number: '',
  


})

const newPlate = ref('')
const addPlate = () => {
  if (!newPlate.value) return

  const formatted = newPlate.value.trim().toUpperCase()

  if (!form.value.plates.includes(formatted)) {
    form.value.plates.push(formatted)
  }

  newPlate.value = ''
}
const removePlate = (index) => {
  form.value.plates.splice(index, 1)
}


const selectPlate = (event) => {
  const plate = event.target.value
  if (!plate) return

  if (!form.value.plates.includes(plate)) {
    form.value.plates.push(plate)
  }

  event.target.value = ''
}

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
  form.value.products = form.value.products.filter(
    p => p.uid !== uid
  )
}


const saveOrder = async () => {
  // 🔹 Si es documento global, asignamos valores a todos los productos
  form.value.products.forEach(p => {
    if (form.value.use_global_document) {
      p.document_type = form.value.document_type
      p.document_number = form.value.document_number
    } else {
      p.document_type = p.document_type || ''
      p.document_number = p.document_number || ''
    }
  })


  // 🔹 Petición al backend
  const response = await fetch('http://localhost:8000/api/purchase-order', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(form.value),
  })

  if (!response.ok) {
    const errorData = await response.json()
    console.error('Error backend:', errorData)
    throw new Error(errorData.details || 'Error al guardar la orden')
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
const OrderComplete = computed(() => {

  if (!form.value.id_provider) return false
  if (form.value.plates.length === 0) return false
  if (form.value.products.length === 0) return false

  // Validar documento global
  if (form.value.use_global_document) {
    if (!form.value.document_type || !form.value.document_number) {
      return false
    }
  }

  for (const item of form.value.products) {

    if (
      !item.unit_measure ||
      item.bulk_or_roll_quantity <= 0
    ) {
      return false
    }

    // Validar documento individual SOLO si no es global
    if (!form.value.use_global_document) {
      if (!item.document_type || !item.number) {
        return false
      }
    }
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

      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body py-2">
          <div class="row g-2 align-items-end">

            <div class="col-md-2">
              <label class="form-label small fw-bold">Fecha</label>
              <input class="form-control form-control-sm bg-light" :value="form.date" disabled>
            </div>

            <div class="col-md-2">
              <label class="form-label small fw-bold">Estatus</label>
              <input class="form-control form-control-sm bg-light" 
                     :value="form.status" disabled>
            </div>

            <div class="col-md-3">
              <label class="form-label small fw-bold">Proveedor</label>
              
              <select class="form-select form-select-sm custom-select-green"v-model="form.id_provider">                
                <option value="" class="text-dark">Seleccionar</option>
                <option v-for="p in providers":key="p.id_provider":value="p.id_provider"class="text-dark">
                  {{ p.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-body py-2">
          <div class="row g-2 align-items-end">
            <div class="col-md-2">
              <label class="form-label small fw-bold">Placas</label>

              <select
                class="form-select form-select-sm mb-1"
                :disabled="!form.id_provider"
                @change="selectPlate"
              >
                <option value="">Seleccionar placa</option>
                <option
                  v-for="plate in plates"
                  :key="plate"
                  :value="plate"
                >
                  {{ plate }}
                </option>
              </select>
              <div class="d-flex gap-1">
                <input
                  type="text"
                  class="form-control form-control-sm"
                  v-model="newPlate"
                  :disabled="!form.id_provider"
                  placeholder="Nueva placa"
                  @keyup.enter="addPlate"
                />
                <button
                  type="button"
                  class="btn btn-sm btn-primary"
                  @click="addPlate"
                  :disabled="!newPlate"
                >
                  +
                </button>
              </div>

              <div class="mt-2 d-flex flex-wrap gap-1">
                <span
                  v-for="(plate, index) in form.plates"
                  :key="index"
                  class="badge bg-success d-flex align-items-center"
                >
                  {{ plate }}
                  <button
                    type="button"
                    class="btn-close btn-close-white btn-sm ms-1"
                    style="font-size: 8px"
                    @click="removePlate(index)"
                  ></button>
                </span>
              </div>
            </div>

            <div class="col-md-2">
              <label class="form-label small fw-bold">Estado</label>
              <input class="form-control form-control-sm bg-light"
                    :value="form.state || 'N/A'"
                    disabled>
            </div>

            <div class="col-md-3 d-flex align-items-center mt-3">
              <input 
                type="checkbox"
                class="form-check-input me-2"
                v-model="form.use_global_document"
              >
              <label class="small fw-bold">
                Usar mismo documento para todos los productos
              </label>
            </div>

            <div v-if="form.use_global_document" class="col-md-2">
              <label class="form-label small fw-bold">Tipo Doc</label>
              <select class="form-select form-select-sm custom-select-green"
                      v-model="form.document_type">
                <option value="">Seleccione</option>
                <option v-for="d in documentTypes"
                        :key="d.value"
                        :value="d.value">
                  {{ d.label }}
                </option>
              </select>
            </div>

            <div v-if="form.use_global_document" class="col-md-2">
              <label class="form-label small fw-bold">Número Doc</label>
              <input type="text"
                    class="form-control form-control-sm custom-select-green"
                    v-model="form.document_number"
                    placeholder="Ej: 1234">
            </div>



          </div>
        </div>
      </div>

      <!-- ================= BUSCADOR ================= -->
      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body py-2">
          <label class="form-label small fw-bold">Buscar producto</label>
          <input type="text"
                 class="form-control form-control-sm custom-select-green"
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
                <th width="55%">Descripción</th>
                <th width="10%">Agregar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id_product">
                <td>{{ product.title }}</td>
                <td>{{ product.code }}</td>
                <td class="text-truncate">{{ product.description }}</td>
                <td>
                  <button class="btn btn-sm btn-success" @click="addProduct(product)">
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
            <thead style="background:#2e7d32;" class="form-label small fw-bold">
              <tr>
                <th width="10%">Clave</th>
                <th width="15%">Descripción</th>
                <th width="10%">Unidad</th>
                <th width="6%">Bultos</th>
                <th width="6%">Pzas</th>
                <th width="10%">Orden</th>
                <th width="6%">NC</th>
                <th width="8%">Lote</th>
                <th v-if="!form.use_global_document" width="10%">Tipo Doc</th>
                <th v-if="!form.use_global_document" width="10%">No Doc</th>
                <th width="4%"></th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="item in form.products" :key="item.uid">
                <td>{{ item.code }}</td>
                <td class="text-truncate">{{ item.description }}</td>

                <td>
                  <select class="form-select form-select-sm compact-input custom-select-green"v-model="item.unit_measure">
                    <option value="">Seleccione</option>
                    <option v-for="u in unitMeasures":key="u.value":value="u.value">
                      {{ u.label }}
                    </option>
                  </select>
                </td>

                <td>
                  <input type="number"class="form-control form-control-sm compact-input custom-select-green"v-model="item.bulk_or_roll_quantity">
                </td>

                <td>
                  <input type="number"class="form-control form-control-sm compact-input custom-select-green"v-model="item.individual_quantity">
                </td>

                <td>
                  <input type="text"class="form-control form-control-sm compact-input custom-select-green"v-model="item.document_number" placeholder="Ej: OC-1234">
                </td>

                <td>
                  <input type="checkbox" class="form-check-input custom-select-green" v-model="item.non_conformity">
                </td>

                <td>
                  <input type="text"class="form-control form-control-sm compact-input custom-select-green"v-model="item.lot" placeholder="Ej: LOTE-5678">
                </td>
                <td v-if="!form.use_global_document">
                  <select v-model="item.document_type" class="form-select form-select-sm">
                    <option value="">Seleccione</option>
                    <option v-for="d in documentTypes"
                            :key="d.value"
                            :value="d.value">
                      {{ d.label }}
                    </option>
                  </select>
                </td>

                <td v-if="!form.use_global_document">
                  <input type="text"
                        v-model="item.number"
                        class="form-control form-control-sm"
                        placeholder="Ej: DOC-9012">
                </td>

                <td>
                  <button class="btn btn-sm btn-outline-danger " @click="removeProduct(item.uid)">
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
      <div class="d-flex justify-content-end gap-2">
        <button 
          class="btn btn-outline-secondary btn-sm"
          @click="goDashboard"
        >
          Cancelar
        </button>

        <button v-if="OrderComplete"class="btn btn-success btn-sm"@click="goTest"> 
          Guardar orden
        </button>

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

.compact-input {
  padding: 4px 6px !important;
  font-size: 12px;
  border-radius: 6px;
}

.custom-select-green {
  background-color: #ecfdf5 !important;
  border: 1px solid #86efac !important;
  color: #065f46 !important;
}

.custom-select-green:focus {
  border-color: #22c55e !important;
  box-shadow: 0 0 0 0.1rem rgba(34,197,94,.25) !important;
}

.table td,
.table th {
  padding: 6px !important;
  font-size: 12px;
  vertical-align: middle !important;
}

.table thead {
  font-size: 12px;
  letter-spacing: 0.3px;
}

.table thead tr {
  background: linear-gradient(90deg, #166534, #15803d);
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

html, body {
  height: 100%;
  overflow: hidden;
}

</style>

