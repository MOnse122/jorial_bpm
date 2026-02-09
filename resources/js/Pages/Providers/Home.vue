<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const providers = ref([])
const loading = ref(true)

const form = ref({
  name: '',
  plates: '',
  state: '',
})

const editing = ref(null)

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

const store = async () => {
  try {
    const response = await fetch(
      'http://localhost:8000/api/providers',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(form.value),
      }
    )

    if (!response.ok) {
      const error = await response.json()
      console.error('Error 422:', error)
      return
    }

    const data = await response.json()
    providers.value.push(data)

    form.value = { name: '', plates: '', state: '' }

  } catch (err) {
    console.error(err)
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
          plates: editing.value.plates,
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
      <h2 class="fw-bold fs-4 mb-0">
        Proveedores
      </h2>
    </template>

    <div class="container py-4">

      <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Nuevo proveedor</h5>
        </div>

        <div class="card-body">
          <form @submit.prevent="store" class="row g-3">

            <div class="col-md-4">
              <label class="form-label">Nombre</label>
              <input
                v-model="form.name"
                type="text"
                class="form-control"
                placeholder="Nombre del proveedor"
                required
              />
            </div>

            <div class="col-md-4">
              <label class="form-label">Placas</label>
              <input
                v-model="form.plates"
                type="text"
                class="form-control"
                placeholder="ABC-123"
                required
              />
            </div>

            <div class="col-md-4">
              <label class="form-label">Estado</label>
              <select
                v-model="form.state"
                class="form-select"
                required
              >
                <option disabled value="">Selecciona</option>
                <option>NORMAL</option>
                <option>REDUCIDA</option>
                <option>SEVERA</option>
              </select>
            </div>

            <div class="col-12 text-end">
              <button class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar
              </button>
            </div>

          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Listado de proveedores</h5>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Placas</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="text-center py-4">
                  <div class="spinner-border text-primary"></div>
                </td>
              </tr>

              <tr v-else-if="providers.length === 0">
                <td colspan="5" class="text-center text-muted py-4">
                  No hay proveedores registrados
                </td>
              </tr>

              <tr v-else v-for="p in providers" :key="p.id_provider">
                <td>{{ p.id_provider }}</td>
                <td>{{ p.name }}</td>
                <td>{{ p.plates }}</td>
                <td>
                  <span
                    class="badge"
                    :class="{
                      'bg-success': p.state === 'NORMAL',
                      'bg-warning text-dark': p.state === 'REDUCIDA',
                      'bg-danger': p.state === 'SEVERA'
                    }"
                  >
                    {{ p.state }}
                  </span>
                </td>
                <td class="text-center">
                  <button
                    class="btn btn-sm btn-outline-primary me-2"
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

      <div
        class="modal fade show d-block"
        v-if="editing"
        tabindex="-1"
        style="background: rgba(0,0,0,.5)"
      >
        <div class="modal-dialog">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">Editar proveedor</h5>
              <button class="btn-close" @click="editing = null"></button>
            </div>

            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input v-model="editing.name" class="form-control" />
              </div>

              <div class="mb-3">
                <label class="form-label">Placas</label>
                <input v-model="editing.plates" class="form-control" />
              </div>

              <div class="mb-3">
                <label class="form-label">Estado</label>
                <select v-model="editing.state" class="form-select">
                  <option>NORMAL</option>
                  <option>REDUCIDA</option>
                  <option>SEVERA</option>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn btn-secondary" @click="editing = null">
                Cancelar
              </button>
              <button class="btn btn-success" @click="update">
                Actualizar
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
 