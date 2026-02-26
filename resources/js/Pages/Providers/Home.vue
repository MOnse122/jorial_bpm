<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const providers = ref([])
const loading = ref(true)
const errorMessage = ref('')
const editing = ref(null)
const errors = ref({})
const success = ref('')
const form = ref({
  name: '',
  plates: '',
  state: '',
})
const resetForm = () => {
  form.value.name = ''
  form.value.plates = []
  form.value.state = ''
}

const store = async () => {
  try {
    errorMessage.value = ''
    success.value = ''
    errors.value = {}

    // 🔥 Convertir string a array
    const formattedPlates = form.value.plates
      ? form.value.plates
          .split(',')
          .map(p => p.trim())
          .filter(p => p.length > 0)
      : []

    const payload = {
      name: form.value.name,
      state: form.value.state,
      plates: formattedPlates
    }

    const response = await fetch('http://localhost:8000/api/providers', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(payload),
    })


    const data = await response.json()

    // 🔹 Validación Laravel (422)
    if (response.status === 422) {
      console.error('Numero de Placas incorrecto', data.errors)
      errors.value = data.errors || {}
      return
    }

    // 🔹 Registro duplicado (409)
    if (response.status === 409) {
      errorMessage.value = data.message
      return
    }

    // 🔹 Otro error servidor
    if (!response.ok) {
      errorMessage.value = 'Error del servidor'
      return
    }

    // 🔹 ÉXITO
    success.value = 'Proveedor creado correctamente'
    providers.value.push(data)

    // Limpiar formulario correctamente
    resetForm()

    await fetchProviders()

  } catch (e) {
    errorMessage.value = 'Error de conexión con el servidor'
  }
}


const fetchProviders = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/providers')
    providers.value = await response.json()
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const destroy = async (id) => {
  try {
    await fetch(`http://localhost:8000/api/providers/${id}`, {
      method: 'DELETE',
    })

    providers.value = providers.value.filter(
      p => p.id_provider !== id
    )
  } catch (err) {
    console.error(err)
  }
    await fetchProviders()

}

const update = async () => {
  try {
    const response = await fetch(
      `http://localhost:8000/api/providers/${editing.value.id_provider}`,
      {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          name: editing.value.name,
          plates: editing.value.plates
            ? editing.value.plates
                .split(',')
                .map(p => p.trim())
                .filter(p => p.length > 0)
            : [],

          state: editing.value.state,
        }),
      }
    )

    if (!response.ok) {
      const error = await response.json()
      console.error('Error update 422:', error)
      return
    }

    const data = await response.json()

    const index = providers.value.findIndex(
      p => p.id_provider === data.id_provider
    )

    providers.value[index] = data
    editing.value = null

  } catch (err) {
    console.error(err)
  }

  await fetchProviders()
}


onMounted(fetchProviders)
</script>
<template>
  <AppLayout>
    <template #header>
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-dark text-lg">Administración de Proveedores</h2>
        <span class="badge bg-emerald-light text-emerald px-3 py-2 rounded-pill small">
          {{ providers.length }} Proveedores Activos
        </span>
      </div>
    </template>

    <div class="container-fluid py-3 bg-lightgray main-scroll">

      <div class="card shadow-sm mb-4 compact-card border-0">
        <div class="card-header bg-emerald text-white py-3">
          <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Proveedor</h6>
        </div>

        <div class="card-body py-4 px-4">
          <form @submit.prevent="store" class="row g-3 align-items-end">

            <div class="col-md-4">
              <label class="form-label-small">Nombre / Razón Social</label>
              <input
                v-model="form.name"
                type="text"
                class="form-control custom-input"
                placeholder="Ej. Insumos Globales S.A."
                required
              />
            </div>

            <div class="col-md-3">
              <label class="form-label-small">Placas de Transporte</label>
              <input
                v-model="form.plates"
                type="text"
                class="form-control custom-input"
                :class="{ 'is-invalid': errors.plates }"
                placeholder="ABC-1234, XYZ-987"
              />
              <div v-if="errors.plates" class="invalid-feedback">
                {{ errors.plates[0] }}
              </div>
            </div>

            <div class="col-md-3">
              <label class="form-label-small">Nivel de Inspección</label>
              <select
                v-model="form.state"
                class="form-select custom-input"
                required
              >
                <option disabled value="">Seleccionar nivel...</option>
                <option value="NORMAL">🟢 NORMAL</option>
                <option value="REDUCIDA">🟡 REDUCIDA</option>
                <option value="SEVERA">🔴 SEVERA</option>
              </select>
            </div>

            <div class="col-md-2 text-end">
              <button class="btn btn-emerald w-100 fw-bold shadow-sm py-2">
                <i class="bi bi-save me-1"></i> Guardar
              </button>
            </div>

          </form>

          <div v-if="errorMessage" class="alert alert-danger-subtle mt-3 py-2 small border-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ errorMessage }}
          </div>

          <div v-if="success" class="alert alert-success-subtle mt-3 py-2 small border-success">
            <i class="bi bi-check-circle-fill me-2"></i> {{ success }}
          </div>
        </div>
      </div>

      <div class="card shadow-sm compact-card border-0 overflow-hidden">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold">Listado de proveedores registrados</h6>
          <i class="bi bi-filter-right fs-5"></i>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="bg-light text-uppercase">
                <tr class="small fw-bold text-secondary">
                  <th class="ps-4 py-3" width="30%">Proveedor</th>
                  <th width="25%">Placas Registradas</th>
                  <th width="20%" class="text-center">Estado</th>
                  <th width="25%" class="text-end pe-4">Acciones</th>
                </tr>
              </thead>

              <tbody class="border-top-0">
                <tr v-if="loading">
                  <td colspan="4" class="py-5 text-center">
                    <div class="spinner-border text-emerald" role="status"></div>
                  </td>
                </tr>

                <tr v-else-if="providers.length === 0">
                  <td colspan="4" class="text-muted py-5 text-center">
                    <i class="bi bi-people fs-1 opacity-25 d-block mb-2"></i>
                    No hay proveedores registrados actualmente
                  </td>
                </tr>

                <tr v-else v-for="p in providers" :key="p.id_provider" class="provider-row">
                  <td class="ps-4">
                    <div class="fw-bold text-dark">{{ p.name }}</div>
                    <div class="text-muted small" style="font-size: 10px;">ID: #{{ p.id_provider }}</div>
                  </td>
                  <td>
                    <div class="d-flex flex-wrap gap-1">
                      <span v-for="plate in p.plates" :key="plate.plate_number" class="badge bg-light text-dark border small fw-normal">
                        {{ plate.plate_number }}
                      </span>
                      <span v-if="!p.plates?.length" class="text-muted small italic">Sin placas</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <span
                      class="badge rounded-pill px-3 py-2 fw-bold"
                      :class="{
                        'bg-success-subtle text-success border border-success': p.state === 'NORMAL',
                        'bg-warning-subtle text-warning-emphasis border border-warning': p.state === 'REDUCIDA',
                        'bg-danger-subtle text-danger border border-danger': p.state === 'SEVERA'
                      }"
                    >
                      {{ p.state }}
                    </span>
                  </td>
                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm shadow-sm rounded">
                      <button
                        class="btn btn-white border fw-semibold"
                        @click="editing = { ...p }"
                      >
                        <i class="bi bi-pencil-square me-1 text-primary"></i> Editar
                      </button>
                      <button
                        class="btn btn-white border"
                        @click="destroy(p.id_provider)"
                      >
                        <i class="bi bi-trash3 text-danger"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div
        class="modal fade show d-block"
        v-if="editing"
        tabindex="-1"
        style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);"
      >
        <div class="modal-dialog modal-dialog-centered modal-sm">
          <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header bg-light py-3 border-bottom-0">
              <h6 class="modal-title fw-bold">Editar Proveedor</h6>
              <button class="btn-close shadow-none" @click="editing = null"></button>
            </div>

            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label-small">Nombre</label>
                <input v-model="editing.name" class="form-control custom-input" />
              </div>

              <div class="mb-3">
                <label class="form-label-small">Placas (Separadas por coma)</label>
                <input v-model="editing.plates" class="form-control custom-input" />
              </div>

              <div class="mb-2">
                <label class="form-label-small">Estado / Nivel</label>
                <select v-model="editing.state" class="form-select custom-input">
                  <option value="NORMAL">NORMAL</option>
                  <option value="REDUCIDA">REDUCIDA</option>
                  <option value="SEVERA">SEVERA</option>
                </select>
              </div>
            </div>

            <div class="modal-footer border-top-0 p-3">
              <button class="btn btn-light btn-sm fw-bold px-3" @click="editing = null">
                Cancelar
              </button>
              <button class="btn btn-emerald btn-sm fw-bold px-3 shadow-sm" @click="update">
                Actualizar Cambios
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
/* Estructura General */
.main-scroll {
  height: calc(100vh - 110px);
  overflow-y: auto;
}
.bg-lightgray { background-color: #f1f5f9; }

/* Tarjetas y UI */
.compact-card {
  border-radius: 16px;
  background: white;
}

/* Colores de Marca */
.bg-emerald { background-color: #009975 !important; }
.text-emerald { color: #009975 !important; }
.bg-emerald-light { background-color: #ecfdf5; }
.btn-emerald { 
  background-color: #009975; 
  color: white; 
  border: none;
  transition: all 0.3s;
}
.btn-emerald:hover { 
  background-color: #007d60; 
  color: white; 
  transform: translateY(-1px);
}

/* Formulario */
.form-label-small {
  font-size: 11px;
  text-transform: uppercase;
  font-weight: 800;
  color: #64748b;
  margin-bottom: 5px;
  display: block;
}
.custom-input {
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  padding: 8px 12px;
  font-size: 13px;
  transition: all 0.2s;
}
.custom-input:focus {
  border-color: #009975;
  box-shadow: 0 0 0 3px rgba(0, 153, 117, 0.1);
}

/* Tabla */
.table thead th {
  font-size: 11px;
  letter-spacing: 0.05em;
  color: #475569;
}
.provider-row { transition: background 0.2s; cursor: default; }
.provider-row:hover { background-color: #f8fafc; }

/* Botones Clean */
.btn-white { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
.btn-white:hover { background: #f8fafc; color: #1e293b; border-color: #cbd5e1; }

/* Badges Sutiles (Bootstrap 5.3) */
.alert-success-subtle { background-color: #dcfce7; color: #166534; }
.alert-danger-subtle { background-color: #fee2e2; color: #991b1b; }

/* Tipografía */
h2 { font-size: 22px; letter-spacing: -0.025em; }
</style>
 