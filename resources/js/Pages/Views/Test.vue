<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const id_purchase_order = page.props.id_purchase_order

const purchaseOrder = ref<any>(null)
const checks = ref<any[]>([])
const result = ref('')
const observations = ref('')
const loading = ref(false)

const date = computed(() => new Date().toISOString().split('T')[0]) 
const activeTab = ref('INGRESO')


onMounted(async () => {
  try {

    // ================= CARGAR ORDEN DE COMPRA =================
    const resOrder = await axios.get(
      `/api/purchase-order/${id_purchase_order}`
    )
    console.log(resOrder.data)
    purchaseOrder.value = resOrder.data

  } catch (error: any) {
    console.error(error.response?.data || error)
  }
})
  // ================= CARGAR CRITERIOS =================
  onMounted(async () => {
    const res = await axios.get('/api/test')

    checks.value = res.data.map((c: any) => ({
      id_criterio_detail: c.id_criterio_detail,
      label: c.criterio.description,
      sector: c.sector,
      value: ''
    }))
  })

  const tabSectorMap: Record<string, string> = {
    INGRESO: 'A',
    BPM: 'B',
    INSPECCIÓN: 'C',
    CONDICIONES: 'D',
    EXCLUSIVO: 'E'
  }

  const filteredChecks = computed(() => {
    return checks.value.filter(
      c => c.sector === tabSectorMap[activeTab.value]
    )
  })

  // ================= EVALUAR =================

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

  checks.value.forEach(c => {

    if (c.value === '') {
      hasEmpty = true
      return
    }

    if (c.value === 'NA') {
      return
    }

    const weight = sectorWeight[c.sector] || 0
    maxScore += weight

    if (c.value === 'SI') {
      totalScore += weight
    }

    if (c.sector === 'E' && c.value === 'NO') {
      hasExclusiveNo = true
    }

  })

  if (hasEmpty) {
    result.value = 'INCOMPLETO'
    return
  }

  if (hasExclusiveNo) {
    result.value = 'NO CONFORMIDAD'
    return
  }

  if (maxScore === 0) {
    result.value = 'INCOMPLETO'
    return
  }

  const percentage = (totalScore / maxScore) * 100

  if (percentage >= 91) result.value = 'APROBADO'
  else if (percentage >= 70) result.value = 'CONDICIONAL'
  else result.value = 'RECHAZADO'
}

  // ================= GUARDAR =================
  const saveTest = async () => {
    evaluate()

    loading.value = true

    try {
      await axios.post('/api/test', {
        id_purchase_order,
        observations: observations.value,
        details: checks.value.map(c => ({
          id_criterio_detail: c.id_criterio_detail,
          score: c.value
        }))
      })

      alert('Evaluación guardada correctamente')

    } catch (error: any) {
      console.error(error.response?.data || error)
      alert('Error al guardar evaluación')
    }

    loading.value = false
  }


  </script>

  <template>
    <AuthenticatedLayout>
      <template #header>
        <h2 class="fw-bold mb-0">Check In Proveedor</h2>
      </template>

      <div class="container-fluid py-3">
        <div class="row">
          <div class="col-md-6">
            <div class="card shadow-sm mb-3">
              <div class="card-body">
                <h5 class="card-title">Orden de Compra</h5>

                <p class="mb-1">
                  <strong>Proveedor:</strong>
                  {{ purchaseOrder?.provider?.name || 'N/A' }}
                </p>

                <p class="mb-1">
                  <strong>Folio:</strong>
                  {{ purchaseOrder?.folio || 'N/A' }}
                </p>

                <p class="mb-0">
                  <strong>Estado:</strong>
                  {{ purchaseOrder?.status || 'N/A' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- ================= TABS ================= -->
        <div class="card shadow-sm mb-3">
          <div class="card-body py-2">
            <ul class="nav nav-tabs small">
              <li class="nav-item"
                  v-for="tab in ['INGRESO','BPM','INSPECCIÓN','CONDICIONES','EXCLUSIVO']"
                  :key="tab">
                <button
                  class="nav-link"
                  :class="{ active: activeTab === tab }"
                  @click="activeTab = tab"
                >
                  {{ tab }}
                </button>
              </li>
            </ul>
          </div>
        </div>


        <!-- ================= CHECKLIST ================= -->
        <div class="card shadow-sm mb-3">
          <div class="card-body p-0">

            <table class="table table-sm table-bordered mb-0 text-center align-middle small">
              <thead class="table-light">
                <tr>
                  <th width="55%">Dato</th>
                  <th width="15%">Cumple</th>
                  <th width="15%">No cumple</th>
                  <th width="15%">N/A</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(item,index) in filteredChecks" 
                  :key="item.id_criterio_detail"
                >
                  <td class="text-start">{{ item.label }}</td>

                  <td>
                    <input
                      type="radio"
                      class="form-check-input"
                      :name="'check'+item.id_criterio_detail"
                      value="SI"
                      v-model="item.value"
                    >
                  </td>

                  <td>
                    <input
                      type="radio"
                      class="form-check-input"
                      :name="'check'+item.id_criterio_detail"
                      value="NO"
                      v-model="item.value"
                    >
                  </td>

                  <td>
                    <input
                      type="radio"
                      class="form-check-input"
                      :name="'check'+item.id_criterio_detail"
                      value="NA"
                      v-model="item.value"
                    >
                  </td>
                </tr>
              </tbody>


            </table>

          </div>
        </div>

        <!-- ================= RESULTADO ================= -->
        <div class="d-flex align-items-center gap-3 mb-3">

          <button
            class="btn btn-primary btn-sm"
            @click="evaluate"
          >
            Evaluar
          </button>

          <div class="small">
            Resultado:
            <span
              class="fw-bold ms-2"
              :class="{
                'text-success': result === 'APROBADO',
                'text-danger': result.includes('RECHAZADO') || result === 'NO CONFORMIDAD',
                'text-warning': result === 'INCOMPLETO' || result === 'CONDICIONAL'
              }"
            >
              {{ result }}
            </span>
          </div>



        </div>

        <!-- ================= OBSERVACIONES ================= -->
        <div class="card shadow-sm mb-3">
          <div class="card-body py-2">
            <label class="form-label small fw-bold">
              Observaciones
            </label>
            <textarea
              class="form-control form-control-sm"
              rows="3"
              v-model="observations"
            ></textarea>
          </div>
        </div>

        <!-- ================= BOTONES ================= -->
        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-outline-secondary btn-sm">
            Cancelar
          </button>

          <button
            class="btn btn-success btn-sm"
            :disabled="loading"
            @click="saveTest"
          >
            {{ loading ? 'Guardando...' : 'Guardar Evaluación' }}
          </button>
        </div>

      </div>
    </AuthenticatedLayout>
  </template>

  <style scoped>
  .table td,
  .table th {
    padding: 6px !important;
    font-size: 12px;
  }

  h2 {
    font-size: 20px;
  }
  </style>
