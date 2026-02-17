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
      <h2 class="fw-bold mb-0">Proveedores</h2>
    </template>

    <div class="container-fluid py-3">

      <!-- ================= NUEVO PROVEEDOR ================= -->
      <div class="card shadow-sm mb-3 compact-card">
        <div class="card-header bg-primary text-white py-2">
          <h6 class="mb-0">Nuevo proveedor</h6>
        </div>

        <div class="card-body py-2">
          <form @submit.prevent="store" class="row g-2 align-items-end small">

            <div class="col-md-4">
              <label class="form-label small fw-bold">Nombre</label>
              <input
                v-model="form.name"
                type="text"
                class="form-control form-control-sm"
                placeholder="Nombre del proveedor"
                required
              />
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold">Placas</label>
              <input
                v-model="form.plates"
                type="text"
                class="form-control form-control-sm"
                :class="{ 'is-invalid': errors.plates }"
                placeholder="ABC-123"
              />
              <div v-if="errors.plates" class="invalid-feedback">
                {{ errors.plates[0] }}
              </div>
            </div>

            <div class="col-md-3">
              <label class="form-label small fw-bold">Estado</label>
              <select
                v-model="form.state"
                class="form-select form-select-sm"
                required
              >
                <option disabled value="">Selecciona</option>
                <option>NORMAL</option>
                <option>REDUCIDA</option>
                <option>SEVERA</option>
              </select>
            </div>

            <div class="col-md-1 text-end">
              <button class="btn btn-primary btn-sm">
                Guardar
              </button>
            </div>

          </form>

          <div v-if="errorMessage" class="alert alert-danger mt-2 py-1 small">
            {{ errorMessage }}
          </div>

          <div v-if="success" class="alert alert-success mt-2 py-1 small">
            {{ success }}
          </div>
        </div>
      </div>

      <!-- ================= LISTADO ================= -->
      <div class="card shadow-sm compact-card">
        <div class="card-header bg-dark text-white py-2">
          <h6 class="mb-0">Listado de proveedores</h6>
        </div>

        <div class="card-body p-0">
          <table class="table table-sm table-hover mb-0 text-center align-middle small">
            <thead class="table-light">
              <tr>
                <th width="25%">Nombre</th>
                <th width="20%">Placas</th>
                <th width="20%">Estado</th>
                <th width="25%">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="py-3">
                  <div class="spinner-border spinner-border-sm text-primary"></div>
                </td>
              </tr>

              <tr v-else-if="providers.length === 0">
                <td colspan="5" class="text-muted py-2">
                  No hay proveedores registrados
                </td>
              </tr>

              <tr v-else v-for="p in providers" :key="p.id_provider">
                <td>{{ p.name }}</td>
                <td>{{ p.plates?.map(plate => plate.plate_number).join(', ') }}</td>
                <td>
                  <span
                    class="badge small"
                    :class="{
                      'bg-success': p.state === 'NORMAL',
                      'bg-warning text-dark': p.state === 'REDUCIDA',
                      'bg-danger': p.state === 'SEVERA'
                    }"
                  >
                    {{ p.state }}
                  </span>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-outline-primary me-1"
                    @click="editing = { ...p }"
                  >
                    Editar
                  </button>
                  <button
                    class="btn btn-sm btn-outline-danger"
                    @click="destroy(p.id_provider)"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ================= MODAL ================= -->
      <div
        class="modal fade show d-block"
        v-if="editing"
        tabindex="-1"
        style="background: rgba(0,0,0,.5)"
      >
        <div class="modal-dialog modal-sm">
          <div class="modal-content compact-card">

            <div class="modal-header py-2">
              <h6 class="modal-title">Editar proveedor</h6>
              <button class="btn-close btn-sm" @click="editing = null"></button>
            </div>

            <div class="modal-body small">
              <div class="mb-2">
                <label class="form-label small fw-bold">Nombre</label>
                <input v-model="editing.name" class="form-control form-control-sm" />
              </div>

              <div class="mb-2">
                <label class="form-label small fw-bold">Placas</label>
                <input v-model="editing.plates" class="form-control form-control-sm" />
              </div>

              <div class="mb-2">
                <label class="form-label small fw-bold">Estado</label>
                <select v-model="editing.state" class="form-select form-select-sm">
                  <option>NORMAL</option>
                  <option>REDUCIDA</option>
                  <option>SEVERA</option>
                </select>
              </div>
            </div>

            <div class="modal-footer py-2">
              <button class="btn btn-secondary btn-sm" @click="editing = null">
                Cancelar
              </button>
              <button class="btn btn-success btn-sm" @click="update">
                Actualizar
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

 