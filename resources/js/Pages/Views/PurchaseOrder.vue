<script setup>
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, onMounted, watch, computed } from 'vue'


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
const allProducts = ref([])

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

  const formatted = newPlate.value.trim().toUpperCase()

  if (!formatted) return

  const plateRegex = /^[A-Z0-9-]{5,7}$/

  if (!plateRegex.test(formatted)) {
    alert('Formato de placa inválido. Ej: ABC123 o ABC-123')
    return
  }

  if (form.value.plates.includes(formatted)) {
    alert('La placa ya fue agregada')
    return
  }

  form.value.plates.push(formatted)

  newPlate.value = ''
  selectedPlate.value = ''
}

const removePlate = (index) => {
  form.value.plates.splice(index, 1)
}

// ================== PRODUCTOS ==================

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

watch(() => filters.value.search, fetchProducts)

const addProduct = (product) => {
  form.value.products.push({
    uid: crypto.randomUUID(),
    id_product: product.id_product,
    code: product.code || '',
    title: product.title || '',
    description: product.description || '',
    unit_measure: '',
    bulk_or_roll_quantity: 0,
    individual_quantity: 0,
    document_number: form.value.use_global_document ? form.value.global_doc_number : '',
    document_type: form.value.use_global_document ? form.value.global_doc_type : 'FACTURA',
    non_conformity: false,
    num_order: '',
    lot: '',
  })
  allProducts.value = []
  filters.value.search = ''
}

const removeProduct = (uid) => {
  form.value.products = form.value.products.filter(p => p.uid !== uid)
}

// ================== GUARDAR ORDEN ==================
const saveOrder = async () => {
  // Sincronizar datos globales a cada producto antes de enviar
  if (form.value.use_global_document) {
    form.value.products.forEach(p => {
      p.document_number = form.value.document_number;
      p.document_type = form.value.document_type;
    });
  }

  const response = await fetch(`http://localhost:8000/api/purchase-order`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(form.value),
  });
  // ... resto del código
}
onMounted(() => {
  fetchProducts()
  fetchProviders()
})

const goTest = async () => {
  try {
    const response = await saveOrder();
    console.log("Respuesta del servidor:", response);

    // Verificamos si la ID viene dentro de .data o directo en el objeto
    const id = response.data ? response.data.id_purchase_order : response.id_purchase_order;

    if (id) {
      window.location.href = `/purchase-order/${id}/test`;
    } else {
      throw new Error("No se recibió un ID válido de la orden.");
    }

  } catch (error) {
    console.error(error);
    alert('No se pudo guardar la orden: ' + error.message);
  }
}

const goView = async () => {
  try {
    const data = await saveOrder()
    console.log("RESPUESTA COMPLETA:", data)

    window.location.href = `/purchase-order`

  } catch (error) {
    alert('No se pudo guardar la orden. ' + error.message)
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
    if (!form.value.use_global_document && (!item.document_type || !item.document_number)) return false
  }

  return true
})

const onlyNumbers = (event) => {
  const keysAllowed = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'];
  if (!keysAllowed.includes(event.key)) {
    event.preventDefault();
  }
};


const onFocus = (item, field) => {
  if (item[field] === 0) {
    item[field] = ''; // Al entrar, si es 0, lo dejamos vacío para escribir fácil
  }
};
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="fw-bold mb-0">Orden de Pedido</h2>
    </template>

    <div class="container-fluid py-3 bg-lightgray rounded-3 main-scroll">

      <div class="card shadow-sm mb-3 p-3 compact-card">
        <div class="row g-3">

          <div class="col-md-3" >
            <label class="form-label small fw-semibold text-muted">PROVEEDOR</label>
            <select
              class="form-select form-select-sm"
              v-model="form.id_provider"
              :class="{'is-valid': form.id_provider, 'is-invalid': !form.id_provider}"
            >
              <option value="">Seleccionar proveedor</option>
              <option v-for="p in providers" :key="p.id_provider" :value="p.id_provider">{{ p.name }}</option>
            </select>
          </div>

          <div class="col-md-5">
            <label class="form-label small fw-semibold text-muted">PLACAS ASIGNADAS</label>

            <!-- Placas agregadas -->
            <div 
              class="plates-container mb-2"
              :class="{ 'border border-danger': form.plates.length === 0 && form.id_provider }"
            >
              <span 
                v-for="(plate, index) in form.plates" 
                :key="index"
                class="plate-chip"
              >
                {{ plate }}

                <button 
                  type="button"
                  class="remove-plate"
                  @click="removePlate(index)"
                >
                  ×
                </button>
              </span>

              <small v-if="form.plates.length === 0" class="text-danger fst-italic">
                No hay placas agregadas
              </small>
            </div>

            <!-- Selector -->
            <div class="input-group input-group-sm">
              <select
                class="form-select"
                v-model="selectedPlate"
                :disabled="!form.id_provider"
                :class="{
                  'is-valid': form.plates.length > 0,
                  'is-invalid': form.id_provider && form.plates.length === 0
                }"
              >
                <option value="">Seleccionar placa...</option>

                <option 
                  v-for="plate in plates" 
                  :key="plate" 
                  :value="plate"
                >
                  {{ plate }}
                </option>

                <option value="__new__">Registrar otra...</option>
              </select>

              <input 
                v-if="isAddingNewPlate"
                type="text"
                class="form-control"
                v-model="newPlate"
                placeholder="Escribir placa"
                @keyup.enter="addPlate"
                @input="newPlate = newPlate.toUpperCase()"
                :class="{
                  'is-valid': newPlate && form.plates.length > 0,
                  'is-invalid': newPlate && form.plates.length === 0
                }"
              />

              <button 
                class="btn btn-primary"
                type="button"
                @click="addPlate"
                :disabled="isAddingNewPlate ? !newPlate.trim() : !selectedPlate"
              >
                +
              </button>
            </div>
          </div>
        </div>

        <hr class="my-3 opacity-25">
      <div class="row align-items-center g-3 mt-1">
        <div class="col-md-6">
          <div class="form-check form-switch custom-switch">
            <input 
              v-tooltip="'Permite asignar un único documento a todos los productos de la orden.'"
              class="form-check-input shadow-none cursor-pointer" 
              type="checkbox" 
              id="globalDoc" 
              v-model="form.use_global_document"
              
            >
            <label class="form-check-label small fw-bold text-dark cursor-pointer" for="globalDoc">
              Usar mismo documento para todos los productos
            </label>
          </div>
        </div>

        <transition name="fade">
          <div v-if="form.use_global_document" class="col-md-6 d-flex gap-2">
            <div class="input-group input-group-sm flex-nowrap">
              <span class="input-group-text bg-light text-muted fw-bold border-end-0">Tipo</span>
              <select 
                class="form-select shadow-none border-primary-subtle" 
                v-model="form.document_type"
                :disabled="!form.use_global_document"
                :class="{'is-valid': form.document_type, 'is-invalid': !form.document_type}"
              >
                <option value="">Seleccione</option>
                <option v-for="d in documentTypes" :key="d.value" :value="d.value">{{ d.label }}</option>
              </select>
            </div>

            <div class="input-group input-group-sm flex-nowrap">
              <span class="input-group-text bg-light text-muted fw-bold border-end-0">No. Doc</span>
              <input 
                type="text" 
                class="form-control shadow-none border-primary-subtle" 
                v-model="form.document_number"
                placeholder="Ej. F-123"
                :class="{'is-valid': form.document_number, 'is-invalid': !form.document_number}"
                @input="form.document_number = form.document_number.toUpperCase()"
              >
            </div>
          </div>
        </transition>
      </div>
      </div>

      <div class="card shadow-sm mb-3 compact-card border-0">
        <div class="card-body py-2 px-3 ">
          <label class="form-label small fw-bold text-muted text-uppercase mb-1">Añadir productos a la orden</label>
          <div class="position-relative">
            <input type="text" class="form-control form-control-sm border-primary" 
                   placeholder="Escriba código o descripción para buscar..." 
                   v-model="filters.search"
                   >
            
            <div v-if="allProducts.length > 0" class="list-group position-absolute w-100 z-3 shadow mt-1">
              <button v-for="p in allProducts" :key="p.id_product" 
                      @click="addProduct(p)"
                      class="list-group-item list-group-item-action py-2 small d-flex justify-content-between"
                      >
                <span><strong>{{ p.code }}</strong> - {{ p.title }}</span>
                <span class="badge bg-primary">+</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3 compact-card overflow-hidden">
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0 text-center align-middle">
            <thead class="table-success small fw-bold">
              <tr>
              <th class="ps-3" width="100">SKU</th>
              <th width="200">Producto</th>
              <th width="100">Unidad</th>
              <th width="90">Bultos</th>
              <th width="90">Piezas</th>
              <th width="120">Orden / Doc</th>
              <th width="5">NC</th>
              <th width="120" class="text-center">Lote</th>
              <template v-if="!form.use_global_document">
                <th width="90">Tipo Doc</th>
                <th width="110">No. Doc</th>
              </template>
              <th width="50"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in form.products" :key="item.uid">
                <td class="fw-bold">{{ item.code }}</td>
                <td class="text-start">
                  <div class="text-truncate" style="max-width: 250px;" :title="item.title">
                    {{ item.title }}
                  </div>
                </td>
                
                <td>
                  <select class="form-select form-select-sm" v-model="item.unit_measure" :class="{'is-invalid': !item.unit_measure, 'is-valid': item.unit_measure}">
                    <option value="">Seleccione</option>
                    <option v-for="u in unitMeasures" :key="u.value" :value="u.value">{{ u.label }}</option>
                  </select>
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm" @focus="onFocus(item, 'bulk_or_roll_quantity')" @keydown="onlyNumbers" v-model="item.bulk_or_roll_quantity" :class="{'is-valid': item.bulk_or_roll_quantity > 0, 'is-invalid': !item.bulk_or_roll_quantity}">
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm " @focus="onFocus(item, 'individual_quantity')" @keydown="onlyNumbers" v-model="item.individual_quantity" :class="{'is-valid': item.individual_quantity > 0, 'is-invalid': !item.individual_quantity}">
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm text-uppercase" placeholder="Ej. 66567" @focus="onFocus(item, 'num_order')" v-model="item.num_order " :class="{'is-valid': item.num_order, 'is-invalid': !item.num_order}">
                </td>
                <td>
                  <input type="checkbox" class="form-check-input" v-model="item.non_conformity">
                </td> 
                <td>
                  <input type="text" class="form-control form-control-sm text-uppercase" v-model="item.lot" placeholder="Ej. 0978688" :class="{'is-valid': item.lot, 'is-invalid': !item.lot}">
                </td>

                <template v-if="!form.use_global_document">
                  <td>
                    <select class="form-select form-select-sm" v-model="item.document_type">
                      <option value="">...</option>
                      <option v-for="d in documentTypes" :key="d.value" :value="d.value">{{ d.label }}</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" class="form-control form-control-sm" @focus="onFocus(item, 'document_number')" @keydown="onlyNumbers" v-model="item.document_number" :class="{'is-valid': item.document_number, 'is-invalid': !item.document_number} " placeholder="Ej. 9887982">
                  </td>
                </template>
                <td>
                  <button class="btn btn-sm btn-outline-danger border-0" @click="removeProduct(item.uid)">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </td>
              </tr>

              <tr v-if="form.products.length === 0">
                <td :colspan="form.use_global_document ? 9 : 11" class="text-muted py-3">
                  No hay productos en la lista
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-outline-secondary btn-sm px-4" @click="goDashboard">Cancelar</button>
        <button v-if="OrderComplete" class="btn btn-success btn-sm px-4 shadow-sm" @click="goTest">
          Guardar y continuar
        </button>
        <button v-if="OrderComplete" class="btn btn-success btn-sm px-4 shadow-sm" @click="goView">
          Guardar
        </button>
      </div>

    </div>
  </AppLayout>
</template>

<style scoped>

/* Contenedor principal de los chips */
.plates-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  min-height: 40px;
  padding: 10px;
  background-color: #f8fafc; /* Gris muy claro */
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  align-items: center;
}

/* Estilo de cada placa (Chip) */
.plate-chip {
  display: inline-flex;
  align-items: center;
  background-color: #009975; /* Verde esmeralda para coincidir con tu sistema */
  color: white;
  padding: 4px 12px;
  border-radius: 50px; /* Forma de píldora */
  font-size: 0.85rem;
  font-weight: 600;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.plate-chip:hover {
  transform: translateY(-1px);
}

/* Botón de eliminar dentro del chip */
.remove-plate {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.2rem;
  line-height: 1;
  margin-left: 8px;
  padding: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: color 0.2s ease;
}

.remove-plate:hover {
  color: #ffcbd1; /* Rojo suave al pasar el mouse */
}

/* Ajustes para el input-group para que se vea integrado */
.input-group-sm .form-select, 
.input-group-sm .form-control, 
.input-group-sm .btn {
  border-radius: 6px;
}

/* Animación simple cuando aparece una placa */
.plate-chip {
  animation: fadeInScale 0.2s ease-out;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.main-scroll {
  height: calc(100vh - 120px);
  overflow-y: auto;
  padding-right: 4px;
}

/* Scroll */
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

/* Cards */
.compact-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.2s ease-in-out;
  background-color: #ffffff;
}

.compact-card:hover {
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}

/* ===== TABLA ===== */
.table :deep(td),
.table :deep(th) {
  padding: 6px !important;
  font-size: 12px;
  vertical-align: middle !important;
}

.table :deep(thead) {
  font-size: 12px;
  letter-spacing: 0.3px;
}

/* ===== BOTONES ===== */
:deep(.btn-success) {
  background-color: #009975 !important;
  border-color: #009975 !important;
  border-radius: 6px;
}

:deep(.btn-success:hover) {
  background-color: #15803d !important;
  border-color: #15803d !important;
}

:deep(.btn-outline-danger) {
  border-radius: 6px;
}

/* ===== TITULO ===== */
h2 {
  font-size: 18px;
  letter-spacing: 0.5px;
}

/* ===== BACKGROUND ===== */
.bg-lightgray {
  background-color: #f3f4f6;
}

/* ===== VALIDACIONES ===== */
:deep(.is-valid) {
  border-color: #5abd7e !important;
  background-color: #ecfdf5 !important;
}

:deep(.is-invalid) {
  border-color: #ff0000 !important;
  background-color: #fee2e2 !important;
}
</style>

