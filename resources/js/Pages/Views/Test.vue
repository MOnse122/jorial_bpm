<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const activeTab = ref('INGRESO')

const checks = ref([
  { label: 'Registro en bitácora de ingreso', value: '' },
  { label: 'Identificación vigente del proveedor', value: '' },
  { label: 'Lavado y/o desinfección de manos al ingreso', value: '' },
  { label: 'Notifica enfermedad, herida o lesión visible (si aplica)', value: '' },
])

const result = ref('')

const evaluate = () => {
  const hasNo = checks.value.some(c => c.value === 'NO')
  const hasEmpty = checks.value.some(c => c.value === '')

  if (hasEmpty) result.value = 'INCOMPLETO'
  else if (hasNo) result.value = 'RECHAZADO'
  else result.value = 'APROBADO'
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="fw-bold mb-0">Check In Proveedor</h2>
    </template>

    <div class="container-fluid py-3">

      <!-- ================= TABS ================= -->
      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body py-2">
          <ul class="nav nav-tabs small">
            <li class="nav-item" v-for="tab in ['INGRESO','BPM','INSPECCIÓN','CONDICIONES','EXCLUSIVO']" :key="tab">
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
      <div class="card shadow-sm mb-3 compact-card">
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
              <tr v-for="(item,index) in checks" :key="index">
                <td class="text-start">{{ item.label }}</td>

                <td>
                  <input type="radio"
                         class="form-check-input"
                         :name="'check'+index"
                         value="SI"
                         v-model="item.value">
                </td>

                <td>
                  <input type="radio"
                         class="form-check-input"
                         :name="'check'+index"
                         value="NO"
                         v-model="item.value">
                </td>

                <td>
                  <input type="radio"
                         class="form-check-input"
                         :name="'check'+index"
                         value="NA"
                         v-model="item.value">
                </td>
              </tr>

              <tr v-if="checks.length === 0">
                <td colspan="4" class="text-muted py-2">
                  No hay datos
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
          Resultado del <strong><i>check in</i></strong>:
          <span
            class="fw-bold ms-2"
            :class="{
              'text-success': result === 'APROBADO',
              'text-danger': result === 'RECHAZADO',
              'text-warning': result === 'INCOMPLETO'
            }"
          >
            {{ result }}
          </span>
        </div>

      </div>

      <!-- ================= OBSERVACIONES ================= -->
      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-body py-2">
          <label class="form-label small fw-bold">
            Escriba sus observaciones aquí.
          </label>
          <textarea
            class="form-control form-control-sm"
            rows="3"
          ></textarea>
        </div>
      </div>

      <!-- ================= BOTONES ================= -->
      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-outline-secondary btn-sm">
          Cancelar
        </button>

        <button class="btn btn-success btn-sm">
          Avanzar muestreo
        </button>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.compact-card {
  border-radius: 8px;
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
