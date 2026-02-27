<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

/* ================== PROPS ================== */
const props = defineProps({
  order: Object,
})

/* ================== DATOS BASE ================== */

// Soporte si viene como Laravel Resource o objeto directo
const orderData = computed(() => props.order?.data || props.order)
const orderId = computed(() => orderData.value?.id_purchase_order)

const providers = ref([])
const loading = ref(false)
const allProducts = ref([])
const filters = ref({ search: '' })

/* ================== PLACAS ================== */
const plates = ref([]) // Placas que pertenecen al proveedor seleccionado
const selectedPlate = ref('')
const isAddingNewPlate = ref(false)
const newPlate = ref('')

const currentFolio = computed(() => orderData.value?.folio || '')

/* ================== FORMULARIO REACTIVO ================== */


const form = ref({
  id_purchase_order: orderId.value,
  date: orderData.value?.date ?? '',
  status: orderData.value?.status ?? 'PENDIENTE',
  id_provider: orderData.value?.provider?.id_provider 
               ?? orderData.value?.id_provider 
               ?? '',
  plates: orderData.value?.plates?.map(p => p.plate_number) ?? [],

  products: orderData.value?.details?.map(d => ({
    uid: crypto.randomUUID(),
    id_product: d.id_product || d.product?.id_product,
    code: d.product?.code ?? '',
    description: d.product?.description ?? '',
    unit_measure: d.unit_measure ?? '',
    bulk_or_roll_quantity: d.bulk_or_roll_quantity ?? 0,
    individual_quantity: d.individual_quantity ?? 0,
    document_number: d.document_number ?? '',
    document_type: d.document_type ?? 'FACTURA',
    non_conformity: Boolean(d.non_conformity),
    lot: d.lot ?? '',
  })) ?? [],
  
  // Campos auxiliares para documento global (opcional)
  use_global_document: false,
  global_doc_number: '',
  global_doc_type: 'FACTURA'
})

/* ================== WATCHERS ================== */

// Cargar placas cuando cambia el proveedor
watch(() => form.value.id_provider, (newId) => {
  if (!newId) {
    plates.value = []
    return
  }
  const provider = providers.value.find(p => p.id_provider == newId)
  plates.value = provider?.plates?.map(p => p.plate_number) ?? []
}, { immediate: true })

// Manejo de selección de placas
watch(selectedPlate, (val) => {
  if (val === '__new__') {
    isAddingNewPlate.value = true
  } else if (val) {
    if (!form.value.plates.includes(val)) {
      form.value.plates.push(val)
    }
    selectedPlate.value = ''
  }
})

// Aplicar documento global a todos los productos
watch(() => form.value.use_global_document, (val) => {
  if (!val) return
  applyGlobalDocument()
})

const applyGlobalDocument = () => {
  form.value.products.forEach(p => {
    p.document_number = form.value.global_doc_number
    p.document_type = form.value.global_doc_type
  })
}

/* ================== ACCIONES PLACAS ================== */

const addPlate = () => {
  const plateToAdd = newPlate.value.trim().toUpperCase()
  if (plateToAdd && !form.value.plates.includes(plateToAdd)) {
    form.value.plates.push(plateToAdd)
  }
  newPlate.value = ''
  isAddingNewPlate.value = false
  selectedPlate.value = ''
}

const removePlate = (index) => {
  form.value.plates.splice(index, 1)
}

/* ================== BUSCADOR PRODUCTOS ================== */

const fetchProducts = async () => {
  if (filters.value.search.length < 2) {
    allProducts.value = []
    return
  }

  try {
    const response = await fetch(`http://localhost:8000/api/products?search=${filters.value.search}`)
    const data = await response.json()
    allProducts.value = data.data || data
  } catch (err) {
    console.error('Error buscando productos', err)
  }
}

watch(() => filters.value.search, fetchProducts)

const addProduct = (product) => {
  form.value.products.push({
    uid: crypto.randomUUID(),
    id_product: product.id_product,
    code: product.code || '',
    description: product.description || '',
    unit_measure: '',
    bulk_or_roll_quantity: 0,
    individual_quantity: 0,
    document_number: form.value.use_global_document ? form.value.global_doc_number : '',
    document_type: form.value.use_global_document ? form.value.global_doc_type : 'FACTURA',
    non_conformity: false,
    lot: '',
  })
  allProducts.value = []
  filters.value.search = ''
}

const removeProduct = (uid) => {
  form.value.products = form.value.products.filter(p => p.uid !== uid)
}

/* ================== CARGA INICIAL ================== */

onMounted(async () => {
  try {
    loading.value = true
    const resProviders = await fetch('http://localhost:8000/api/providers')
    const data = await resProviders.json()
    providers.value = data.data ?? data
  } catch (err) {
    console.error('Error cargando proveedores', err)
  } finally {
    loading.value = false
  }
})

/* ================== UPDATE ================== */

const updating = ref(false)

const updateOrder = () => {
  updating.value = true

  // Limpiamos el objeto para el envío
  const payload = JSON.parse(JSON.stringify(form.value))

  router.put(route('purchase-order.update', orderId.value), payload, {
    onSuccess: () => {
      updating.value = false
      alert('Orden actualizada correctamente')
    },
    onError: (errors) => {
      updating.value = false
      console.error(errors)
      alert('Error al actualizar la orden')
    },
  })
}

/* ================== VALIDACIÓN Y CONSTANTES ================== */

const OrderComplete = computed(() => {
  return form.value.id_provider && 
         form.value.plates.length > 0 && 
         form.value.products.length > 0 &&
         form.value.products.every(p => p.unit_measure && p.bulk_or_roll_quantity > 0)
})

const unitMeasures = [
  { value: 'kg', label: 'Kilogramos' },
  { value: 'pz', label: 'Piezas' },
  { value: 'mil', label: 'Millares' },
]

const goDashboard = () => {
  router.visit(route('dashboard'))
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0">Actualizacion de Orden de Pedido</h2>
      </div>
    </template>

    <div class="container-fluid py-3 bg-lightgray rounded-3 main-scroll">

      <div class="card shadow-sm mb-3 p-3 rounded-3 border-0">
        <div class="row g-3">
          
          <div class="col-md-2">
            <label class="form-label small fw-bold text-muted text-uppercase">Folio</label>
            <input class="form-control form-control-sm bg-light fw-bold" :value="currentFolio" disabled>
          </div>

          <div class="col-md-2">
            <label class="form-label small fw-bold text-muted text-uppercase">Fecha</label>
            <input class="form-control form-control-sm bg-light" :value="form.date" disabled>
          </div>

          <div class="col-md-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Proveedor</label>
            <select class="form-select form-select-sm" v-model="form.id_provider">
              <option value="">Seleccionar Proveedor</option>
              <option v-for="p in providers" :key="p.id_provider" :value="p.id_provider">
                {{ p.name }}
              </option>
            </select>
          </div>

          <div class="col-md-5">
            <label class="form-label small fw-bold text-muted text-uppercase">Placas (Gestión)</label>
            <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-white min-h-custom">
              <span v-for="(plate, index) in form.plates" :key="index" 
                    class="badge bg-success d-flex align-items-center gap-2 py-2 px-3 rounded-pill">
                {{ plate }}
                <i class="fa-solid fa-xmark cursor-pointer" @click="removePlate(index)"></i>
              </span>
              
              <div class="d-flex gap-1 ms-auto">
                <select class="form-select form-select-sm border-0 bg-transparent" v-model="selectedPlate">
                  <option value="">+ Placa</option>
                  <option v-for="p in plates" :key="p" :value="p">{{ p }}</option>
                  <option value="__new__">Escribir nueva...</option>
                </select>
                
                <input v-if="isAddingNewPlate" v-model="newPlate" type="text" 
                       class="form-control form-control-sm border-primary" 
                       placeholder="Nueva placa..." @keyup.enter="addPlate">
              </div>
            </div>
          </div>

        </div>

        <div class="row g-3 mt-2 pt-2 border-top">
           <div class="col-md-4 d-flex align-items-center">
             <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" v-model="form.use_global_document" id="globalDoc">
                <label class="form-check-label small fw-bold" for="globalDoc">Usar mismo documento para todo</label>
             </div>
           </div>
           <div v-if="form.use_global_document" class="col-md-8 d-flex gap-2">
              <select class="form-select form-select-sm w-25" v-model="form.global_doc_type" @change="applyGlobalDocument">
                <option value="FACTURA">FACTURA</option>
                <option value="REMISION">REMISIÓN</option>
              </select>
              <input type="text" class="form-control form-control-sm w-50" 
                     placeholder="Número de documento" 
                     v-model="form.global_doc_number" @input="applyGlobalDocument">
           </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3 border-0 compact-card">
        <div class="card-body py-2">
          <label class="form-label small fw-bold text-muted">AÑADIR PRODUCTOS A LA ORDEN</label>
          <div class="position-relative">
            <input type="text" class="form-control form-control-sm" 
                   placeholder="Buscar por código o descripción..." 
                   v-model="filters.search">
            
            <div v-if="allProducts.length > 0" class="list-group position-absolute w-100 z-3 shadow mt-1">
              <button v-for="p in allProducts" :key="p.id_product" 
                      @click="addProduct(p)"
                      class="list-group-item list-group-item-action py-2 small d-flex justify-content-between align-items-center">
                <span><strong>{{ p.code }}</strong> - {{ p.description }}</span>
                <span class="badge bg-primary">+</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3 border-0 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0 text-center align-middle small">
            <thead class="bg-dark text-white">
              <tr>
                <th>Clave</th>
                <th width="30%">Descripción</th>
                <th>Unidad</th>
                <th width="80">Bultos</th>
                <th width="80">Pzas</th>
                <th width="150">Documento</th>
                <th width="120">Lote</th>
                <th>NC</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in form.products" :key="item.uid" class="bg-white">
                <td class="fw-bold text-primary">{{ item.code }}</td>
                <td class="text-start ps-3">{{ item.description }}</td>
                <td>
                  <select v-model="item.unit_measure" class="form-select form-select-sm">
                    <option value="">Seleccione</option>
                    <option v-for="u in unitMeasures" :key="u.value" :value="u.value">{{ u.label }}</option>
                  </select>
                </td>
                <td><input type="number" v-model="item.bulk_or_roll_quantity" class="form-control form-control-sm text-center"></td>
                <td><input type="number" v-model="item.individual_quantity" class="form-control form-control-sm text-center"></td>
                <td>
                  <div class="input-group input-group-sm">
                    <select class="form-select" v-model="item.document_type" :disabled="form.use_global_document">
                      <option value="FACTURA">F</option>
                      <option value="REMISION">R</option>
                    </select>
                    <input type="text" class="form-control w-50" v-model="item.document_number" :disabled="form.use_global_document">
                  </div>
                </td>
                <td><input type="text" v-model="item.lot" class="form-control form-control-sm" placeholder="Lote"></td>
                <td><input type="checkbox" v-model="item.non_conformity" class="form-check-input"></td>
                <td>
                  <button class="btn btn-sm text-danger" @click="removeProduct(item.uid)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="form.products.length === 0">
                <td colspan="9" class="py-5 text-muted">
                  <i class="fa-solid fa-box-open d-block mb-2" style="font-size: 2rem;"></i>
                  No hay productos registrados en esta orden.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-3 pb-5">
        <button class="btn btn-outline-secondary btn-sm px-4 shadow-sm" @click="goDashboard">
          Cancelar
        </button>

        <button class="btn btn-success btn-sm px-4 fw-bold shadow-sm"
                :disabled="!OrderComplete || updating"
                @click="updateOrder">
          <span v-if="updating" class="spinner-border spinner-border-sm me-1"></span>
          {{ updating ? 'Actualizando...' : 'Guardar Cambios' }}
        </button>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.main-scroll {
  height: calc(100vh - 120px);
  overflow-y: auto;
  padding-right: 5px;
}
.main-scroll::-webkit-scrollbar {
  width: 6px;
}
.main-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.bg-lightgray {
  background-color: #f8fafc;
}
.min-h-custom {
  min-height: 45px;
}
.cursor-pointer {
  cursor: pointer;
}
.z-3 {
  z-index: 1050;
}
.table thead th {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 12px 5px;
}
.table td {
  padding: 8px 5px;
}
.compact-card {
  border: 1px dashed #cbd5e1;
}
</style>