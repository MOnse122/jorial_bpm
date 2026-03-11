<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { onMounted, ref, computed, watch } from 'vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'

/* ================= PROPS ================= */
const props = defineProps({
  id_purchase_order: [Number, String],
  id_product: [Number, String],
  order_total_products: [Number, String],
})

/* ================= ESTADO ================= */
const orderData = ref<any>(null)
const loading = ref(true)

/* ================= FORMULARIO ================= */
const form = useForm({
  // MIL STD
  id_purchase_order: props.id_purchase_order,
  id_product: props.id_product,
  
  c1: 1,
  c2: 1,
  c3: 1,
  inspection_level: 'GI',
  sample_size: 0,
  sample_acept: 0,
  sample_reject: 0,
  aql: 4.0,

  // Local Sampling
  width: '',
  length: '',
  thickness: '',
  piece_number: 1,
  seal_resistance: '',
  color_detachment: '',
  result_lote: '',
  result_piece: '',
  observation: '',
})

/* ================= COMPUTED ================= */
const technicalDetails = computed(() => {
  return orderData.value?.orderDetails?.find((d: any) => d.id_product == props.id_product)
})

const totalCalculado = computed(() => {
  return technicalDetails.value?.total_per_product || props.order_total_products || 0
})

const productInfo = computed(() => {
  return orderData.value?.products?.find((p: any) => p.id_product == props.id_product)
})

/* ================= WATCHERS ================= */
// Nivel de inspección
watch(
  [() => form.c1, () => form.c2, () => form.c3, totalCalculado],
  () => {
    const impactos = [form.c1, form.c2, form.c3]

    if (impactos.includes(3)) form.inspection_level = 'GIII'   // 3 = Alto
    else if (impactos.includes(2)) form.inspection_level = 'GII' // 2 = Medio
    else form.inspection_level = 'GI'                           // 1 = Bajo

    // Tamaño de muestra
    const N = Number(totalCalculado.value) || 0
    let muestra = 0
    if (form.inspection_level === 'GIII') muestra = 0.5 * Math.pow(N, 0.6)
    else if (form.inspection_level === 'GII') muestra = 0.32 * Math.pow(N, 0.6)
    else muestra = 0.125 * Math.pow(N, 0.6)

    form.sample_size = Math.min(Math.ceil(muestra), N)
    if (N < 13 && form.sample_size < N) form.sample_size = N
  },
  { immediate: true }
)


// Ac/Re
watch(() => form.sample_size, (N) => {
  const muestra = Number(N) || 0
  if (muestra <= 0) {
    form.sample_acept = 0
    form.sample_reject = 0
    return
  }
  let ac = 0
  if (muestra < 10) ac = 0
  else if (muestra < 50) ac = 1
  else if (muestra < 100) ac = 2
  else ac = Math.floor(muestra / 20)

  form.sample_acept = ac
  form.sample_reject = ac + 3
})

// Resultado lote → accept_reject

/* ================= CARGA DE DATOS ================= */
onMounted(async () => {
  try {
    loading.value = true
    const response = await axios.get(`/mil-std/api/${props.id_purchase_order}/products`)
    orderData.value = response.data.data
  } catch (error) {
    console.error('Error cargando datos:', error)
  } finally {
    loading.value = false
  }
})


/* ================= GUARDAR INSPECCIÓN ================= */
const saveInspection = async () => {
  // Validación mínima
  if (!form.width || !form.length || !form.thickness || !form.seal_resistance || !form.color_detachment || !form.result_lote) {
    alert('Completa todos los campos numéricos de la muestra')
    return
  }
  try {
    form.processing = true;
    await axios.post(
      `/inspection/${props.id_purchase_order}/${props.id_product}`,
      form.data() // <- ¡muy importante!
    )
    alert('¡Inspección guardada correctamente!');
    window.location.href = `/mil-std/${props.id_purchase_order}`;

  } catch (error: any) {
    console.error('Error:', error.response?.data);
    alert('Error al guardar: ' + (error.response?.data?.error || 'Error de validación'));
  } finally {
    form.processing = false;
  }
};

/* ================= IR A DASHBOARD ================= */
const goDashboard = () => {
  window.location.href = `/mil-std/${props.id_purchase_order}`
}


/* ================= RANGOS DE TOLERANCIA (+-10) ================= */
const rangeWidth = computed(() => ({
  min: (Number(productInfo.value?.width) || 0) - 10,
  max: (Number(productInfo.value?.width) || 0) + 10
}))

const rangeLength = computed(() => ({
  min: (Number(productInfo.value?.height) || 0) - 10,
  max: (Number(productInfo.value?.height) || 0) + 10
}))

const rangeThickness = computed(() => ({
  min: (Number(productInfo.value?.cal) || 0) - 50,
  max: (Number(productInfo.value?.cal) || 0) + 50
}))


// Watcher para validar el rango automáticamente
watch([
  () => form.width, 
  () => form.length, 
  () => form.thickness, 
  () => form.seal_resistance, 
  () => form.color_detachment
], () => {
  // Solo validamos si los campos obligatorios tienen valor
  if (form.width && form.length && form.thickness && form.seal_resistance && form.color_detachment) {
    
    const w = Number(form.width);
    const l = Number(form.length);
    const t = Number(form.thickness);

    // 1. Validar Rangos Numéricos
    const isSizeError = 
      w < rangeWidth.value.min || w > rangeWidth.value.max ||
      l < rangeLength.value.min || l > rangeLength.value.max ||
      t < rangeThickness.value.min || t > rangeThickness.value.max;

    // 2. Validar Atributos (Selects)
    // Asumiendo que "No conforme" es lo que detona el rechazo
    const isQualityError = 
      form.seal_resistance === 'No conforme' || 
      form.color_detachment === 'No conforme';

    // 3. Resultado Final (Si falla uno, fallan todos)
    if (isSizeError || isQualityError) {
      form.result_lote = 'RECHAZADO';
      form.result_piece = '3'; // ID de Rechazado
    } else {
      form.result_lote = 'APROBADO';
      form.result_piece = '1'; // ID de Liberado
    }
  }
});

</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-dark">Registro de Muestreo Individual</h2>

      </div>
    </template>

    <div class="container-fluid py-3 bg-lightgray main-scroll">
      <div v-if="loading" class="text-center p-5">
        <div class="spinner-border text-emerald" role="status"></div>
        <p class="mt-2 text-muted">Sincronizando datos...</p>
      </div>

      <div v-else-if="productInfo && technicalDetails">
        
        <div class="card shadow-sm mb-3 compact-card border-top border-4 border-emerald">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <span class="badge bg-emerald-light text-emerald mb-1">Información General</span>
                <h5 class="fw-bold mb-0">FOLIO DE ORDEN: {{ orderData?.folio || '---' }}</h5>
              </div>
              <div class="text-end">
                <span class="info-label-small">Fecha de Orden</span>
                <span class="fw-bold">{{ orderData?.date }}</span>
              </div>
            </div>

            <div v-if="!loading && productInfo" class="row g-3">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm bg-light">
                        <div class="card-body p-3">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">
                                Descripción del Producto
                            </small>
                            <h6 class="fw-bold text-dark mb-1">{{ productInfo.title }}</h6>
                            <p class="text-muted small mb-1">{{ productInfo.description }}</p>
                            <span class="badge bg-secondary">SKU: {{ productInfo.code }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <small class="text-muted d-block mb-1">Lote</small>
                            <span class="h5 fw-bold mb-0 text-dark">{{ technicalDetails?.lot || 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card h-100 border-0 shadow-sm text-center border-start border-3 border-dark">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <small class="text-muted d-block mb-1">Total Unidades</small>
                            <span class="h5 fw-bold mb-0 text-dark-2">{{ totalCalculado }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <small class="text-muted d-block mb-1">Dimensiones</small>
                            <span class="fw-bold">{{ productInfo.width }} x {{ productInfo.height }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <small class="text-muted d-block mb-1">Calibre</small>
                            <span class="fw-bold text-dark">{{ productInfo.cal }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center p-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Cargando información técnica...</p>
            </div>
          </div>
        </div>

        <div class="card shadow-sm compact-card mb-4 ">
          <div class="card-body">
            
            <h6 class="fw-bold mb-3 text-emerald"><i class="bi bi-shield-check me-2 "></i>IMPACTO GLOBAL</h6>
            <div class="table-responsive mb-4">
              <table class="table table-bordered align-middle">
                <thead class="bg-lightgray">
                  <tr class="text-center">
                    <th class="text-start">CRITERIO DE EVALUACIÓN</th>
                    <th width="12%">ALTO</th>
                    <th width="12%">MEDIO</th>
                    <th width="12%">BAJO</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(label, key) in {
                    c1: '1. IMPACTO EN EL TRABAJO, (¿PUEDE CAUSAR RETRABAJO?, ¿PUEDE DETENER EL TRABAJO?)',
                    c2: '2. IMPACTO EN LIMPIEZA E INOCUIDAD, (¿PUEDE ENSUCUAR EL PRODUCTO?, ¿ROMPE REGLAS DE BUENAS PRÁCTICAS DE MANUFACTURA?)',
                    c3: '3. IMPACTO EN ENTREGAS, (¿PUEDE RETRASAR PEDIDOS O HASTA REGRESAR PEDIDOS?, ¿PUEDE AFECTAR AL CLIENTE?)'
                  }" :key="key" class="checklist-row">

                  <td class="fw-semibold small">{{ label }}</td>

                  <td class="text-center">
                  <input type="radio" class="form-check-input" v-model.number="form[key]" :value="3" :name="key" />
                  </td>

                  <td class="text-center">
                  <input type="radio" class="form-check-input" v-model.number="form[key]" :value="2" :name="key" />
                  </td>

                  <td class="text-center">
                  <input type="radio" class="form-check-input" v-model.number="form[key]" :value="1" :name="key" />
                  </td>

                  </tr>
                </tbody>
              </table>
            </div>
            <h6 class="fw-bold mb-3 text-emerald"><i class="bi bi-calculator me-2"></i>RESULTADOS DEL MUESTREO DEL IMPACTO GLOBAL</h6>
            <div class="row g-3 mb-4 p-3 rounded bg-light-emerald border">
              <div class="col-md-2">
                <label class="info-label-small">Nivel Insp.</label>
                <input 
                  type="text" 
                  disabled 
                  class="form-control form-control-sm fw-bold text-uppercase text-center" 
                  :class="{
                    'bg-danger text-white': form.inspection_level === 'GIII',
                    'bg-warning text-dark': form.inspection_level === 'GII',
                    'bg-success text-white': form.inspection_level === 'GI'
                  }"
                  v-model="form.inspection_level" 
                />
              </div>
              <div class="col-md-2">
                <label class="info-label-small">AQL</label>
                <input type="number " disabled class="form-control form-control-sm" v-model="form.aql" />
              </div>
             <div class="col-md-2">
              <label class="info-label-small">Tamaño Muestra</label>
              <input 
                type="number" disabled
                class="form-control form-control-sm " 
                v-model="form.sample_size" 
              />
            </div>

            <div class="col-md-3">
              <label class="info-label-small text-success">Se Acepta (Ac)</label>
              <input 
                type="number" disabled
                class="form-control form-control-sm border-success" 
                :value="form.sample_acept"
                readonly
              />
            </div>

            <div class="col-md-3">
              <label class="info-label-small text-danger">Se Rechaza (Re)</label>
              <input 
                type="number" disabled
                class="form-control form-control-sm border-danger"
                :value="form.sample_reject"
                readonly
              />
            </div>
            </div>

           <h6 class="fw-bold mb-3 text-emerald">
            <i class="bi bi-list-check me-2"></i>REGISTRO DEL MUESTRO LOCAL
          </h6>
      


            <div class="row g-3 mb-4">

            <div class="col-md-3">
              <label class="info-label-small">P1. Ancho (Rango: {{ rangeWidth.min }} - {{ rangeWidth.max }})</label>
              <input 
                type="number" 
                step="0.01"
                class="form-control form-control-sm"
                :class="{ 'is-invalid': form.width && (Number(form.width) < rangeWidth.min || Number(form.width) > rangeWidth.max) }"
                v-model="form.width"
                placeholder="Ej: 70" 
              />
            </div>

            <div class="col-md-3">
              <label class="info-label-small">P1. Largo (Rango: {{ rangeLength.min }} - {{ rangeLength.max }})</label>
              <input 
                type="number" 
                step="0.01"
                class="form-control form-control-sm"
                :class="{ 'is-invalid': form.length && (Number(form.length) < rangeLength.min || Number(form.length) > rangeLength.max) }"
                v-model="form.length"
                placeholder="Ej: 100" 
              />
            </div>

            <div class="col-md-3">
              <label class="info-label-small">P2. Calibre (Rango: {{ rangeThickness.min }} - {{ rangeThickness.max }})</label>
              <input 
                type="number" 
                step="0.01"
                class="form-control form-control-sm"
                :class="{ 'is-invalid': form.thickness && (Number(form.thickness) < rangeThickness.min || Number(form.thickness) > rangeThickness.max) }"
                v-model="form.thickness"
                placeholder="Ej: 250" 
              />
            </div>

              <div 
                class="col-md-3" 
                v-for="(label, key) in {
                  seal_resistance: 'P3. Sellado/Resistencia',
                  color_detachment: 'P4. Desprendimiento color'
                }" 
                :key="key"
              >
                <label class="info-label-small">{{ label }}</label>
                <select class="form-select form-select-sm" v-model="form[key]">
                  <option value="">Seleccionar</option>
                  <option value="Conforme">Conforme</option>
                  <option value="No conforme">No conforme</option>
                  <option value="No Aplica">No Aplica</option>
                </select>
              </div>

            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-7">
                <div class="card shadow-sm compact-card h-100 bg-white border-2 border-danger">
                  <div class="card-body">
                    <label class="form-label small fw-bold text-muted text-uppercase">Observaciones Generales</label>
                    <textarea 
                      class="form-control form-control-sm text-uppercase" 
                      rows="3" 
                      v-model="form.observation" 
                      placeholder="Escriba aquí cualquier anomalía encontrada..."
                    ></textarea>
                  </div>
                </div>
              </div>

              <div class="col-md-5">
                <div class="card shadow-sm compact-card h-100 bg-white border-start border-4" 
                    :class="form.result_lote === 'APROBADO' ? 'border-success' : (form.result_lote === 'RECHAZADO' ? 'border-danger' : 'border-secondary')">
                  <div class="card-body d-flex flex-column justify-content-between">
                    
                    <div>
                      <label class="info-label-small fw-bold">Resultado Final (Automático)</label>
                      <div class="p-2 mb-2 bg-light rounded border text-center">
                        <span class="h5 fw-bold mb-0" :class="form.result_lote === 'APROBADO' ? 'text-success' : 'text-danger'">
                          {{ form.result_lote || 'PENDIENTE' }}
                        </span>
                      </div>
                    </div>

                    <div>
                      <label class="info-label-small fw-bold">Disposición del Material</label>
                      <select class="form-select form-select-sm" v-model="form.result_piece">
                        <option value="">Seleccionar Disposición</option>
                        <option value="1">Liberado</option>
                        <option value="2">Retenido</option>
                        <option value="3">Rechazado</option>
                        <option value="4">En espera de acción del proveedor</option>
                      </select>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <button class="btn btn-outline-secondary btn-sm px-4" @click="goDashboard">Cancelar</button>              
              <button 
                type="button" 
                @click="saveInspection" 
                
                class="btn btn-success px-4 fw-bold" 
                :disabled="form.processing"
              >
                {{ form.processing ? 'Procesando...' : 'GUARDAR INSPECCIÓN' }}
              </button>
            </div>

          </div>
        </div>
      </div>

      <div v-else class="alert alert-warning border-emerald">
        <h6 class="fw-bold text-emerald">Atención</h6>
        No se encontró la información técnica vinculada al ID de producto {{ props.id_product }}.
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Estructura Principal */
.main-scroll {
  height: calc(99vh - 110px); 
  overflow-y: auto;
  padding-right: 1px;
}
.main-scroll::-webkit-scrollbar { width: 6px; }
.main-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

/* Cards y Colores */
.compact-card {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: white;
}
.bg-lightgray { background-color: #f8fafc; }
.border-emerald { border-color: #009975 !important; }
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
}

/* Tablas */
.table th {
  padding: 10px 8px !important;
  letter-spacing: 0.5px;
  color: #475569;
  font-size: 11px;
}
.table td { padding: 8px !important; }
.checklist-row:hover { background-color: #f8fafc; }

/* Radio buttons */
.form-check-input:checked {
  background-color: #009975;
  border-color: #009975;
}

/* Botones */
.btn-success { background-color: #009975; border-color: #009975; }
.btn-success:hover { background-color: #007d60; }
</style>