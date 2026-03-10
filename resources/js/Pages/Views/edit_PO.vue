<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'


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
    title: d.product?.title ?? '',
    id_product: d.id_product || d.product?.id_product,
    code: d.product?.code ?? '',
    description: d.product?.description ?? '',
    unit_measure: d.unit_measure ?? '',
    bulk_or_roll_quantity: d.bulk_or_roll_quantity ?? 0,
    individual_quantity: d.individual_quantity ?? 0,
    document_type: d.document_type ?? 'FACTURA',
    document_number: d.document_number ?? '',
    non_conformity: Boolean(d.non_conformity),
    num_order: d.num_order ?? null,
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
  const plateRegex = /^[A-Z0-9-]{5,7}$/
  if (!plateRegex.test(newPlate.value.trim())) {
    alert('Formato de placa inválido. Ej: ABC123 o ABC-123')
    return
  }
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

const onlyNumbers = (event) => {
  const keysAllowed = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'];
  if (!keysAllowed.includes(event.key)) {
    event.preventDefault();
  }
};

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
    num_order: orderId.value,
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

const updateOrder = (redirect = null) => {
  updating.value = true

  const payload = JSON.parse(JSON.stringify(form.value))

  router.put(route('purchase-order.update', orderId.value), payload, {
    onSuccess: () => {
      updating.value = false

      if (redirect === 'dashboard') {
        window.location.href = '/purchase-order'
      }

      if (redirect === 'view') {
        router.visit(`/purchase-order`)
      }

      if (!redirect) {
        alert('Orden actualizada correctamente')
      }
    },
    onError: (errors) => {
      updating.value = false
      console.error(errors)
      alert('Error al actualizar la orden')
    },
  })
}
const documentTypes = [
  { value: 'FACTURA', label: 'FACTURA' },
  { value: 'REMISION', label: 'REMISION' },
  { value: 'OTRO', label: 'OTRO' },
]
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


</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="fw-bold mb-0">Editar Orden de Pedido</h2>
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
                @keydown="onlyNumbers"
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
                  <input type="number" class="form-control form-control-sm text-center" @focus="onFocus(item, 'bulk_or_roll_quantity')" @keydown="onlyNumbers" v-model="item.bulk_or_roll_quantity" :class="{'is-valid': item.bulk_or_roll_quantity > 0, 'is-invalid': !item.bulk_or_roll_quantity}">
                </td>
                <td>
                  <input type="number" class="form-control form-control-sm text-center fw-bold" @focus="onFocus(item, 'individual_quantity')" @keydown="onlyNumbers" v-model="item.individual_quantity" :class="{'is-valid': item.individual_quantity > 0, 'is-invalid': !item.individual_quantity}">
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm" placeholder="Ej. 66567" @focus="onFocus(item, 'num_order')" @keydown="onlyNumbers" v-model="item.num_order " :class="{'is-valid': item.num_order, 'is-invalid': !item.num_order}">
                </td>
                <td>
                  <input type="checkbox" class="form-check-input" v-model="item.non_conformity">
                </td> 
                <td>
                  <input type="text" class="form-control form-control-sm" v-model="item.lot" @keydown="onlyNumbers" placeholder="Ej. 0978688" :class="{'is-valid': item.lot, 'is-invalid': !item.lot}">
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
        <button 
          type="button"
          class="btn btn-outline-secondary btn-sm px-4" 
          @click="updateOrder('view')"
          :disabled="updating"
        >
          Cancelar
        </button>

        <button 
          type="button"
          class="btn btn-success btn-sm px-4 shadow-sm" 
          @click="updateOrder('dashboard')"
          :disabled="updating || !OrderComplete"
        >
          <span v-if="updating" class="spinner-border spinner-border-sm me-1"></span>
          {{ updating ? 'Guardando...' : 'Guardar' }}
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
