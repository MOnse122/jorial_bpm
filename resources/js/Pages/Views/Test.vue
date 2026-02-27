<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, onMounted, computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'


/* ================= PAGE ================= */

const page = usePage<any>()

const id_purchase_order = page.props.id_purchase_order
const id_plate = page.props.id_plate
const userId = page.props.auth?.user?.id

/* ================= STATE ================= */

const purchaseOrder = ref<any>(null)
const checks = ref<any[]>([])

const result = ref('')
const percentage = ref(0)
const loading = ref(false)

const activeTab = ref('INGRESO')

const form = ref({
  id_purchase_order,
  name_provider: '',
  id_plate,
  observations: '',
  users_id: userId,
  status: 'PENDIENTE1',
})

/* ================= LOAD DATA ================= */

onMounted(async () => {
  try {

    // ORDEN
    const { data: orderData } =
      await axios.get(`/purchase-order/${id_purchase_order}`)

    purchaseOrder.value =
      orderData.data ?? orderData

    // CRITERIOS
    const { data } = await axios.get('/api/test')

    checks.value = data.map((c: any) => ({
      id_criterio_detail: c.id_criterio_detail,
      label: c.criterio.description,
      sector: c.sector,
      value:
        (c.sector === 'D' || c.sector === 'E')
          ? ''
          : 'SI'
    }))

  } catch (error: any) {
    console.error(error.response?.data || error)
  }
})

/* ================= TABS ================= */

const tabSectorMap: Record<string, string> = {
  INGRESO: 'A',
  BPM: 'B',
  INSPECCIÓN: 'C',
  CONDICIONES: 'D',
  EXCLUSIVO: 'E'
}

const filteredChecks = computed(() =>
  checks.value.filter(
    c => c.sector === tabSectorMap[activeTab.value]
  )
)

/* ================= EVALUAR ================= */

const sectorWeight: Record<string, number> = {
  A: 1,
  B: 2,
  C: 3,
  D: 4,
  E: 5
}

const evaluate = () => {

  let totalScore = 0
  let maxScore = 0

  let hasEmpty = false
  let hasExclusiveNo = false

  for (const c of checks.value) {

    if (!c.value) {
      hasEmpty = true
      continue
    }

    if (c.value === 'NA') continue

    const weight = sectorWeight[c.sector] ?? 0
    maxScore += weight

    if (c.value === 'SI')
      totalScore += weight

    if (c.sector === 'E' && c.value === 'NO')
      hasExclusiveNo = true
  }

  if (hasEmpty) {
    result.value = 'INCOMPLETO'
    percentage.value = 0
    return
  }

  if (hasExclusiveNo) {
    result.value = 'NO CONFORMIDAD'
    percentage.value = 0
    return
  }

  if (!maxScore) {
    result.value = 'INCOMPLETO'
    percentage.value = 0
    return
  }

  percentage.value =
    (totalScore / maxScore) * 100

  if (percentage.value >= 91)
    result.value = 'APROBADO'
  else if (percentage.value >= 70)
    result.value = 'CONDICIONAL'
  else
    result.value = 'RECHAZADO'
}

/* ================= GUARDAR ================= */

const saveTest = async (action: 'save' | 'continue') => {
  evaluate(); 

  if (!form.value.name_provider.trim()) {
    alert('Debes escribir el nombre del chofer');
    return;
  }

  if (result.value === 'INCOMPLETO') {
    alert('Debes completar todos los campos antes de guardar');
    return;
  }

  loading.value = true;
  try {
    const payload = {
      ...form.value,
      // FORZAMOS EL STATUS A PENDIENTE1 
      // (Opcional: podrías enviar result.value si prefieres el resultado del test)
      status: 'PENDIENTE1', 
      name_provider: form.value.name_provider.toUpperCase(),
      observations: form.value.observations.toUpperCase(),
      action,
      details: checks.value.map(c => ({
        id_criterio_detail: c.id_criterio_detail,
        sector: c.sector,
        score: c.value
      }))
    };
    console.log(payload.status)

    const { data } = await axios.post('/api/test', payload);

    if (action === 'continue') {
      window.location.href = `/mil-std/${id_purchase_order}`;
    } else {
      alert('✅ Test guardado. El estado ahora es PENDIENTE 1');
      // Opcional: recargar para ver el cambio
      window.location.reload(); 
    }

  } catch (error: any) {
    console.error(error);
    alert('Error al guardar');
  } finally {
    loading.value = false;
  }

}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-dark font-size=0px">
          Check In Proveedor
        </h2>
      </div>
    </template>

    <div class="container-fluid py-3 bg-lightgray main-scroll">
      
      <div class="card shadow-sm mb-3 compact-card border-top border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <div>
              <span class="badge bg-emerald-light text-emerald mb-1">Información General</span>
              <h5 class="fw-bold mb-0">FOLIO DE ORDEN: {{ purchaseOrder?.folio || '---' }}</h5>
            </div>
            <div class="text-end">
              <span class="badge px-3 py-2 rounded-pill" :class="purchaseOrder?.status === 'APROBADO' ? 'bg-success' : 'bg-warning text-dark'">
                {{ purchaseOrder?.status || 'Check-in bpm en proceso...' }}
              </span>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <div class="info-box p-2 rounded bg-light border">
                <label class="info-label-small">Proveedor</label>
                <div class="fw-bold text-dark text-truncate">{{ purchaseOrder?.provider?.name || 'N/A' }}</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="info-box p-2 rounded bg-light border">
                <label class="info-label-small">Placas</label>
                <div class="d-flex flex-wrap gap-1 mt-1">
                  <span v-for="plate in purchaseOrder?.plates" :key="plate.id_plate" class="badge bg-dark-subtle text-dark border-0 small">
                    {{ plate.plate_number }}
                  </span>
                 
                  <span v-if="!purchaseOrder?.plates?.length" class="text-muted small italic">N/A</span>
                </div>
              </div>
            </div>
            <div class="col-md-5">
              <div class="info-box p-2 rounded bg-white border border-emerald">
                <label class="info-label-small text-emerald fw-bold">Nombre del Chofer / Transportista</label>
                <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold" 
                       v-model="form.name_provider" placeholder="Escriba el nombre aquí...">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3 compact-card">
        <div class="bg-light px-3 py-2 border-bottom d-flex align-items-center">
          <i class="fa-solid fa-boxes-stacked me-2 text-muted"></i>
          <span class="fw-bold small text-uppercase">Productos del Pedido</span>
        </div>
        <div class="card-body p-0 overflow-hidden">
          <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
              <tr class="small fw-bold">
                <th class="ps-3">Descripción</th>
                <th>Unidad</th>
                <th class="text-center">Cant.</th>
                <th>Lote</th>
              </tr>
            </thead>
            <tbody class="small">
              <tr v-for="item in purchaseOrder?.order_details || purchaseOrder?.details" :key="item.id_order_detail">
                <td class="ps-3 fw-medium text-dark">{{ item.product?.description }}</td>
                <td class="text-muted">{{ item.unit_measure }}</td>
                <td class="text-center fw-bold">{{ item.bulk_or_roll_quantity }}</td>
                <td><span class="badge bg-light text-dark border font-monospace">{{ item.lot || 'S/L' }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body py-1 px-2">
          <ul class="nav nav-pills nav-justified small gap-1 py-1">
            <li class="nav-item" v-for="tab in ['INGRESO','BPM','INSPECCIÓN','CONDICIONES','EXCLUSIVO']" :key="tab">
              <button class="nav-link py-2" :class="{ active: activeTab === tab }" @click="activeTab = tab">
                {{ tab }}
              </button>
            </li>
          </ul>
        </div>
      </div>

      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body p-0 overflow-hidden">
          <table class="table table-sm table-bordered mb-0 align-middle">
            <thead class="bg-light-emerald">
              <tr class="small text-center">
                <th class="text-start ps-3" width="55%">CRITERIO DE INSPECCIÓN</th>
                <th width="15%">CUMPLE</th>
                <th width="15%">NO CUMPLE</th>
                <th width="15%">N/A</th>
              </tr>
            </thead>
            <tbody class="small text-center">
              <tr v-for="(item, index) in filteredChecks" :key="item.id_criterio_detail" class="checklist-row">
                <td class="text-start ps-3 fw-medium text-dark">{{ item.label }}</td>
                <td><input type="radio" class="form-check-input" :name="'check'+item.id_criterio_detail" value="SI" v-model="item.value"></td>
                <td><input type="radio" class="form-check-input" :name="'check'+item.id_criterio_detail" value="NO" v-model="item.value"></td>
                <td><input type="radio" class="form-check-input" :name="'check'+item.id_criterio_detail" value="NA" v-model="item.value"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-7">
          <div class="card shadow-sm compact-card h-100">
            <div class="card-body">
              <label class="form-label small fw-bold text-muted">OBSERVACIONES GENERALES</label>
              <textarea class="form-control form-control-sm" rows="3" v-model="form.observations" placeholder="Escriba aquí cualquier anomalía encontrada..."></textarea>
            </div>
          </div>
        </div>
        <div class="col-md-5">
          <div class="card shadow-sm compact-card h-100 bg-white border-start border-4" 
               :class="result === 'APROBADO' ? 'border-success' : (result === 'CONDICIONAL' ? 'border-warning' : 'border-danger')">
            <div class="card-body d-flex flex-column justify-content-between">
              <div class="d-flex justify-content-between align-items-center">
                <span class="small fw-bold text-muted">RESULTADO FINAL</span>
                <button class="btn btn-dark btn-sm rounded-pill px-3 py-0" style="font-size: 10px" @click="evaluate">CALCULAR</button>
              </div>
              <div class="text-center py-2">
                <span class="h4 fw-bold mb-0" :class="{
                  'text-success': result === 'APROBADO',
                  'text-danger': result === 'RECHAZADO' || result === 'NO CONFORMIDAD',
                  'text-warning': result === 'CONDICIONAL',
                  'text-secondary': result === 'INCOMPLETO' || !result
                }">
                  {{ result || 'PENDIENTE' }}
                </span>
              </div>
              <div class="d-flex gap-2">
              <button @click="saveTest('save')" :disabled="loading" class="btn btn-success btn-sm flex-fill fw-bold">
                Guardar
              </button >

              <button @click="saveTest('continue')" :disabled="loading" class="btn btn-success btn-sm flex-fill fw-bold">
                Guardar y continuar
              </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Estructura Principal */
.main-scroll {
  height: calc(100vh - 120px);
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
}
.table td { padding: 8px !important; }
.checklist-row:hover { background-color: #f8fafc; }

/* Radio buttons */
.form-check-input:checked {
  background-color: #009975;
  border-color: #009975;
}

/* Tabs */
.nav-pills .nav-link {
  color: #64748b;
  border-radius: 8px;
  font-weight: 600;
}
.nav-pills .nav-link.active {
  background-color: #009975;
  color: white;
}

/* Botones */
.btn-success { background-color: #009975; border-color: #009975; }
.btn-success:hover { background-color: #007d60; }
</style>