<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { onMounted, ref, computed } from 'vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  id_purchase_order: [Number, String],
  id_product: [Number, String] 
})

/* ================= ESTADOS ================= */
const orderData = ref<any>(null)
const loading = ref(true)

/* ================= FORMULARIO INERTIA ================= */
const form = useForm({
  id_purchase_order: props.id_purchase_order,
  id_product: props.id_product,
  impacto_trabajo: '',
  impacto_limpieza: '',
  impacto_entregas: '',
  nivel_inspeccion: 'G1',
  aql: '4.0',
  p1_medidas: '',
  p2_espesor: '',
  p3_sellado: '',
  p4_desprendimiento: '',
  tamano_muestra: 0,
  se_acepta: 0,
  se_rechaza: 0,
  resultado_lote: '',
  disposicion_material: '',
})

/* ================= COMPUTED ================= */
const productInfo = computed(() => {
  return orderData.value?.products?.find((p: any) => p.id_product == props.id_product)
})

const technicalDetails = computed(() => {
  return orderData.value?.orderDetails?.find((d: any) => d.id_product == props.id_product)
})

/* ================= CARGA DE DATOS ================= */
onMounted(async () => {
  try {
    loading.value = true
    const response = await axios.get(`/mil-std/api/${props.id_purchase_order}/products`)
    orderData.value = response.data.data
  } catch (error) {
    console.error("Error cargando datos:", error)
  } finally {
    loading.value = false
  }
})

const saveInspection = () => {
  form.post(route('mil-std.store'), {
    onSuccess: () => alert('Inspección guardada con éxito'),
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-dark">Registro de Muestreo Individual</h2>

      </div>
    </template>

    <div class="container-fluid py-3 bg-lightgray">
      <div v-if="loading" class="text-center p-5">
        <div class="spinner-border text-emerald" role="status"></div>
        <p class="mt-2 text-muted">Sincronizando datos técnicos...</p>
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

            <div class="row g-2">
              <div class="col-md-6">
                <div class="p-2 rounded bg-lightgray border">
                  <span class="info-label-small">Descripción del Producto</span>
                  <span class="fw-bold text-dark d-block">{{ productInfo.title }}</span>
                  <span class="text-muted small">SKU: {{ productInfo.code }}</span>
                </div>
              </div>
              <div class="col-md-2">
                <div class="p-2 rounded bg-lightgray border text-center">
                  <span class="info-label-small">Lote</span>
                  <span class="fw-bold">{{ technicalDetails.lot }}</span>
                </div>
              </div>
              <div class="col-md-2">
                <div class="p-2 rounded bg-lightgray border text-center">
                  <span class="info-label-small">Dimensiones</span>
                  <span class="fw-bold">{{ productInfo.width }} x {{ productInfo.height }}</span>
                </div>
              </div>
              <div class="col-md-2">
                <div class="p-2 rounded bg-lightgray border text-center">
                  <span class="info-label-small">Calibre</span>
                  <span class="fw-bold">{{ productInfo.cal }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm compact-card mb-4 main-scroll">
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
                    impacto_trabajo: '1. IMPACTO EN EL TRABAJO',
                    impacto_limpieza: '2. IMPACTO EN LIMPIEZA E INOCUIDAD',
                    impacto_entregas: '3. IMPACTO EN ENTREGAS'
                  }" :key="key" class="checklist-row">
                    <td class="fw-semibold small">{{ label }}</td>
                    <td class="text-center"><input type="radio" class="form-check-input" v-model="form[key]" value="Alto" :name="key" /></td>
                    <td class="text-center"><input type="radio" class="form-check-input" v-model="form[key]" value="Medio" :name="key" /></td>
                    <td class="text-center"><input type="radio" class="form-check-input" v-model="form[key]" value="Bajo" :name="key" /></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <h6 class="fw-bold mb-3 text-emerald"><i class="bi bi-list-check me-2"></i>PUNTOS DE REVISIÓN</h6>
            <div class="row g-3 mb-4">
              <div class="col-md-3" v-for="(label, key) in {
                p1_medidas: 'P1. Medidas (ancho/largo)',
                p2_espesor: 'P2. Espesor',
                p3_sellado: 'P3. Sellado/Resistencia',
                p4_desprendimiento: 'P4. Desprendimiento color'
              }" :key="key">
                <label class="info-label-small">{{ label }}</label>
                <select class="form-select form-select-sm" v-model="form[key]">
                  <option value="">Seleccionar</option>
                  <option value="Conforme">Conforme</option>
                  <option value="No conforme">No conforme</option>
                </select>
              </div>
            </div>

            <h6 class="fw-bold mb-3 text-emerald"><i class="bi bi-calculator me-2"></i>RESULTADOS DEL MUESTREO (MIL-STD-105D)</h6>
            <div class="row g-3 mb-4 p-3 rounded bg-light-emerald border">
              <div class="col-md-2">
                <label class="info-label-small">Nivel Insp.</label>
                <input type="text" class="form-control form-control-sm" v-model="form.nivel_inspeccion" />
              </div>
              <div class="col-md-2">
                <label class="info-label-small">AQL</label>
                <input type="text" class="form-control form-control-sm" v-model="form.aql" />
              </div>
              <div class="col-md-2">
                <label class="info-label-small">Tamaño Muestra</label>
                <input type="number" class="form-control form-control-sm" v-model="form.tamano_muestra" />
              </div>
              <div class="col-md-3">
                <label class="info-label-small text-success">Se Acepta (Ac)</label>
                <input type="number" class="form-control form-control-sm border-success" v-model="form.se_acepta" />
              </div>
              <div class="col-md-3">
                <label class="info-label-small text-danger">Se Rechaza (Re)</label>
                <input type="number" class="form-control form-control-sm border-danger" v-model="form.se_rechaza" />
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="info-label-small fw-bold">Resultado Final del Lote</label>
                <select class="form-select fw-bold" :class="form.resultado_lote === 'Aprobado' ? 'text-success' : 'text-danger'" v-model="form.resultado_lote">
                  <option value="">Seleccionar Resultado</option>
                  <option value="Aprobado">APROBADO</option>
                  <option value="Rechazado">RECHAZADO</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="info-label-small fw-bold">Disposición del Material</label>
                <select class="form-select" v-model="form.disposicion_material">
                  <option value="">Seleccionar Disposición</option>
                  <option value="Uso">Uso Inmediato</option>
                  <option value="Devolución">Devolución a Proveedor</option>
                  <option value="Destrucción">Destrucción</option>
                </select>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-outline-danger btn-sm px-3">
                <i class="bi bi-plus-circle me-1"></i> No Conformidad
              </button>
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
  height: calc(100vh - 180px); /* Ajustado para que no choque con el header */
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