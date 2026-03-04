<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const providers = ref([])
const loading = ref(true)
const errorMessage = ref('')
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

const showEditModal = ref(false)
const editing = ref({
  id_provider: null,
  name: '',
  plates: '',
  state: ''
})
const openEditModal = (provider) => {
  editing.value = {
    id_provider: provider.id_provider,
    name: provider.name,
    plates: provider.plates
      ? provider.plates.map(p => p.plate_number).join(', ')
      : '',
    state: provider.state
  }

  showEditModal.value = true
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
      console.error(await response.json())
      return
    }

    showEditModal.value = false
    await fetchProviders()

  } catch (err) {
    console.error(err)
  }
}

onMounted(fetchProviders)

</script>

<template>
  <AppLayout>

    <!-- ================= HEADER ================= -->
    <template #header>
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-dark">
          Administración de Proveedores
        </h2>

      </div>
    </template>


    <!-- ================= CONTENIDO ================= -->
    <div class="container-fluid py-3 bg-lightgray main-scroll">

      <!-- ========= FORM CARD ========= -->
      <div class="card compact-card shadow-sm mb-4 border-0">

        <div class="card-header bg-emerald text-white">
          <h6 class="mb-0 fw-bold">
            <i class="bi bi-plus-circle me-2"></i>
            Registrar Nuevo Proveedor
          </h6>
        </div>

        <div class="card-body">

          <form
            @submit.prevent="store"
            class="row g-3 align-items-end"
          >

            <div class="col-md-4">
              <label class="form-label-small">
                Nombre
              </label>

              <input
                v-model="form.name"
                type="text"
                class="form-control custom-input"
                placeholder="Plastic Power S.A."
                required
              />
            </div>


            <div class="col-md-3">
              <label class="form-label-small">
                Placas de Transporte
              </label>

              <input
                v-model="form.plates"
                class="form-control custom-input"
                :class="{ 'is-invalid': errors.plates }"
                placeholder="ABC-1234, XYZ-987"
              />

              <div v-if="errors.plates" class="invalid-feedback">
                {{ errors.plates[0] }}
              </div>
            </div>


            <div class="col-md-3">
              <label class="form-label-small">
                Nivel de Inspección
              </label>

              <select
                v-model="form.state"
                class="form-select custom-input"
                required
              >
                <option disabled value="">
                  Seleccionar nivel...
                </option>
                <option value="NORMAL">NORMAL</option>
                <option value="REDUCIDA"> REDUCIDA</option>
                <option value="SEVERA">SEVERA</option>
              </select>
            </div>


            <div class="col-md-2">
              <button
                class="btn btn-emerald w-100 fw-bold shadow-sm"
              >
                <i class="bi bi-save me-1"></i>
                Guardar
              </button>
            </div>

          </form>


          <!-- ALERTAS -->
          <div
            v-if="errorMessage"
            class="alert alert-danger-subtle mt-3 small"
          >
            {{ errorMessage }}
          </div>

          <div
            v-if="success"
            class="alert alert-success-subtle mt-3 small"
          >
            {{ success }}
          </div>

        </div>
      </div>


      <!-- ========= TABLE CARD ========= -->
      <div class="card compact-card shadow-sm border-0 overflow-hidden">

        <div
          class="card-header bg-dark text-white
                 d-flex justify-content-between align-items-center"
        >
          <h6 class="mb-0 fw-bold">
            Listado de proveedores registrados
          </h6>

          <i class="bi bi-filter-right fs-5"></i>
        </div>


        <div class="card-body p-0">

          <div class="table-responsive">

            <table class="table table-hover mb-0 align-middle">

              <thead class="bg-light">
                <tr class="small fw-bold text-secondary">
                  <th class="ps-4">Proveedor</th>
                  <th>Placas</th>
                  <th class="text-center">Estado</th>
                  <th class="text-end pe-4">Acciones</th>
                </tr>
              </thead>


              <tbody>

                <!-- LOADING -->
                <tr v-if="loading">
                  <td colspan="4" class="text-center py-5">
                    <div class="spinner-border text-emerald"></div>
                  </td>
                </tr>


                <!-- VACÍO -->
                <tr v-else-if="providers.length === 0">
                  <td colspan="4" class="text-center py-5 text-muted">
                    No hay proveedores registrados
                  </td>
                </tr>


                <!-- DATA -->
                <tr
                  v-else
                  v-for="p in providers"
                  :key="p.id_provider"
                  class="provider-row"
                >

                  <td class="ps-4">
                    <div class="fw-bold text-dark">
                      {{ p.name }}
                    </div>
                  </td>


                  <td>
                    <div class="d-flex flex-wrap gap-1">
                      <span
                        v-for="plate in p.plates"
                        :key="plate.plate_number"
                        class="badge bg-light text-dark border"
                      >
                        {{ plate.plate_number }}
                      </span>
                    </div>
                  </td>


                  <td class="text-center">
                    <span
                      class="badge rounded-pill px-3 py-2 fw-bold"
                      :class="{
                        'bg-success-subtle text-success border border-success': p.state==='NORMAL',
                        'bg-warning-subtle text-warning border border-warning': p.state==='REDUCIDA',
                        'bg-danger-subtle text-danger border border-danger': p.state==='SEVERA'
                      }"
                    >
                      {{ p.state }}
                    </span>
                  </td>


                  <td class="text-end pe-4">

                    <div class="btn-group btn-group-sm">

                      <button
                        class="btn btn-white border fw-semibold"
                        @click="openEditModal(p)"
                      >
                       <i class="fa-solid fa-pen" style="color: rgb(121, 164, 112);"></i>
                      </button>

                    </div>

                  </td>

                </tr>

              </tbody>

            </table>

          </div>
        </div>
      </div>

    </div>

    <!-- ================= MODAL EDITAR ================= -->
    <div v-if="showEditModal" class="modal-backdrop-custom">
      <div class="modal-card shadow-lg">

        <div class="modal-header-custom">
          <h5 class="fw-bold mb-0">Editar Proveedor</h5>
          <button class="btn-close" @click="showEditModal = false"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label-small">Nombre</label>
            <input
              v-model="editing.name"
              class="form-control custom-input"
            />
          </div>

          <div class="mb-3">
            <label class="form-label-small">Placas</label>
            <input
              v-model="editing.plates"
              class="form-control custom-input"
            />
          </div>

          <div class="mb-3">
            <label class="form-label-small">Nivel</label>
            <select
              v-model="editing.state"
              class="form-select custom-input"
            >
              <option value="NORMAL">NORMAL</option>
              <option value="REDUCIDA">REDUCIDA</option>
              <option value="SEVERA">SEVERA</option>
            </select>
          </div>

        </div>

        <div class="modal-footer-custom">
          <button
            class="btn btn-secondary btn-sm"
            @click="showEditModal = false"
          >
            Cancelar
          </button>

          <button
            class="btn btn-emerald btn-sm fw-bold"
            @click="update"
          >
            Guardar Cambios
          </button>
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

/* ===== MODAL PERSONALIZADO ===== */
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-card {
  background: white;
  width: 420px;
  border-radius: 16px;
  overflow: hidden;
  animation: fadeInScale 0.2s ease;
}

.modal-header-custom {
  padding: 15px 20px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 20px;
}

.modal-footer-custom {
  padding: 15px 20px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
 