  <script setup>
  import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { ref, onMounted, watch } from 'vue'

  const providers = ref([])
  const loading = ref(true)

  const form = ref({
    id: null,
    provider: '',
    state: '',
    products: [ ],
    item: '',
    unit_measure: '',
    bulk_or_roll_quantity: 0,
    individual_quantity: 0,
    document_number: '',
    non_conformity: false,
    lot: '',  
    document_type: '',
    number: '',
  })



  const fetchProducts = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/products')
      const products = await response.json()
      console.log(products)
    } catch (err) {
      console.error(err)
    }
  }
  const fetchPurchaseOrder = async () => {
    try {
      const id = route.params.id

      const response = await fetch(`http://localhost:8000/api/purchase-orders/${id}`)
      const data = await response.json()
      Object.assign(purchaseOrder, data)
    } catch (err) {
      console.error(err)
    } finally {
      loading.value = false
    }
  }

  const fetchProviders = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/providers')
      const data = await response.json()
      providers.value = data
    } catch (err) {
      console.error(err)
    }
  }

  watch(
    () => form.value.provider,
    (newProviderId) => {
      const selected = providers.value.find(p => p.id_provider === newProviderId)
      form.value.state = selected ? selected.state : ''
    }
  )



  const destroy = async (id) => {
    try {
      await fetch(`http://localhost:8000/api/purchase-orders/${id}`, {
        method: 'DELETE',
      })

      console.log('Orden de compra eliminada correctamente.')
    } catch (err) {
      console.error(err)
    }
  }

  const update = async (id) => {
    try {
      const response = await fetch(
        `http://localhost:8000/api/purchase-orders/${id}`,
        {
          method: 'PUT',
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
      console.log('Orden de compra actualizada:', data)

    } catch (err) {
      console.error(err)
    }
  } 
  onMounted(() => {
    fetchProducts()
    fetchProviders()
    fetchPurchaseOrder()
  })

  </script>

  <template>
    <AppLayout>
      <!-- HEADER -->
      <template #header>
        <h2 class="fw-bold fs-4 mb-0">
          Detalles de la orden de pedido
        </h2>
      </template>

      <div class="container my-4">

        <div class="card mb-4">
          <div class="card-body">

            <div class="row g-3 align-items-center">
              <div class="col-md-6">
                <label class="form-label fw-bold">Proveedor:</label>
                <select class="form-select" v-model="form.provider">
                  <option value="">Seleccionar proveedor</option>
                  <option v-for="p in providers" :key="p.id_provider" :value="p.id_provider">
                    {{ p.name }}
                  </option>
                </select>

              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Estado del proveedor:</label>
                <span class="form-control">{{ form.state || 'N/A' }}</span>
              </div>


              <div class="col-md-9">
                <label class="form-label fw-bold">Ítem:</label>
                <input type="text" class="form-control" placeholder="Buscar ítem" v-model="form.products">
              </div>

              <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                  Buscar
                </button>
              </div>
            </div>
            
          </div>
        </div>


        <div class="card mb-4">
          <div class="card-body p-0">

            <table class="table table-borderless mb-0">
              <thead class="table-light">
                <tr class="text-center">
                  <th>Clave</th>
                  <th>Descripción</th>
                  <th>Agregar</th>
                </tr>
              </thead>
              <tbody class="text-center">
                <tr>
                  <td>----</td>
                  <td>Descripción del producto</td>
                  <td class="text-success fw-bold">+</td>
                </tr>
                <tr>
                  <td>----</td>
                  <td>Descripción del producto</td>
                  <td class="text-success fw-bold">+</td>
                </tr>
              </tbody>
            </table>

          </div>
        </div>

        <div class="card mb-4">
          <div class="card-body p-0">

            <table class="table table-bordered mb-0 align-middle text-center">
              <thead style="background:#8bc34a;">
                <tr>
                  <th>Clave</th>
                  <th>Descripción</th>
                  <th>UOM</th>
                  <th>Cantidad<br>Bultos/Rollos</th>
                  <th>Cantidad<br>en piezas</th>
                  <th>Orden Pedido</th>
                  <th>Orden Compra</th>
                  <th>No. Conformidad</th>
                  <th>Lote</th>
                  <th>Factura / Remisión</th>
                  <th>No. F/R</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>----</td>
                  <td>Descripción larga del producto</td>
                  <td>
                    <select class="form-select form-select-sm">
                      <option>UOM</option>
                    </select>
                  </td>
                  <td>0</td>
                  <td>0</td>
                  <td>----</td>
                  <td>----</td>
                  <td>----</td>
                  <td>----</td>
                  <td>
                    <select class="form-select form-select-sm">
                      <option>Seleccionar</option>
                    </select>
                  </td>
                  <td>----</td>
                  <td>
                    ✏️ 🗑️
                  </td>
                </tr>
              </tbody>
            </table>

          </div>
        </div>


        <div class="d-flex justify-content-end gap-3">
          <button class="btn btn-danger px-4">
            Cancelar
          </button>
          <button class="btn btn-success px-4">
            Siguiente
          </button>
        </div>

      </div>
    </AppLayout>
  </template>
