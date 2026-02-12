  <script setup>
  import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { ref, onMounted, watch } from 'vue'
  import { usePage } from '@inertiajs/vue3'

  const page = usePage()
  const purchaseOrder = ref({})
  const providers = ref([])
  const loading = ref(true)
  const pagination = ref({})
  const filters = ref({
    search: '',
    state: '',
    
  })
  const unitMeasures = ref([
    { value: 'mil', label: 'Millares' },
    { value: 'kg', label: 'Kilogramos' },
    { value: 'pz', label: 'Piezas' },
  ])

  const documentTypes = ref([
    { value: 'FACTURA', label: 'Factura' },
    { value: 'REMISION', label: 'Remisión' },
    { value: 'OTRO', label: 'Otro' },
  ])

  const form = ref({
    provider: '',
    state: '',
    code: '',
    description: '',
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

  const products = ref([])


const currentPage = ref(1)

  const fetchProducts = async () => {
    try {
      loading.value = true

      const params = new URLSearchParams({
        search: filters.value.search,
        state: filters.value.state,
        page: currentPage.value
      }).toString()

      const response = await fetch(
        `http://localhost:8000/api/products?${params}`
      )

      const data = await response.json()

      products.value = data.data
      pagination.value = data

    } catch (err) {
      console.error(err)
    } finally {
      loading.value = false
    }
  }

  watch(filters, fetchProducts, { deep: true })


  const fetchPurchaseOrder = async () => {
    try {
      const id = page.props.id

      if (!id) return

      const response = await fetch(
        `http://localhost:8000/api/purchase-orders/${id}`
      )

      if (!response.ok) {
        throw new Error('Error al obtener la orden')
      }

      purchaseOrder.value = await response.json()

    } catch (err) {
      console.error(err)
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

  const addProduct = (product) => {
    if (!form.value.products.find(p => p.id_product === product.id_product)) {
      form.value.products.push({
        id_product: product.id_product,
        code: product.code,
        description: product.description,
        unit_measure: '',
        bulk_or_roll_quantity: 0,
        individual_quantity: 0,
        document_number: '',
        non_conformity: false,
        lot: '',
        document_type: '',
        number: '',
      })
    }
    console.log('Producto agregado:', product)
  }

  onMounted(() => {
    fetchProducts()
    fetchProviders()
    fetchPurchaseOrder()
  })

  const saveOrder = async () => {
    try {
      const response = await fetch(
        'http://localhost:8000/api/purchase-orders',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(form.value),
        }
      )

      if (!response.ok) {
        throw new Error('Error al guardar la orden')
      }

      const data = await response.json()
      console.log('Orden creada:', data)

    } catch (err) {
      console.error(err)
    }
  }


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
                <input type="text" class="form-control" placeholder="Buscar ítem" v-model="filters.search">
              </div>


            </div>
            
          </div>
        </div>


        <div class="card mb-4">
          <div class="card-body p-0">
            <table class="table table-borderless mb-0">
              <thead class="table-light">
                <tr class="text-center">
                  <th>Título</th>
                  <th>Clave</th>
                  <th>Descripción</th>
                  <th><i class="fa-jelly fa-regular fa-plus"></i></th>
                </tr>
              </thead>

              <tbody class="text-center">
                <tr v-for="product in products" :key="product.id_product">
                  <td>{{ product.title }}</td>
                  <td>{{ product.code }}</td>
                  <td>{{ product.description }}</td>
                  <td>
                    <button class="btn btn-success" @click="addProduct(product)">
                      <i class="fa-jelly fa-regular fa-plus"></i>
                    </button>
                    
                  </td>
                </tr>

                <!-- Opcional: mensaje si no hay productos -->
                <tr v-if="products.length === 0">
                  <td colspan="3" class="text-muted">
                    No hay productos disponibles
                  </td>
                </tr>
              </tbody>
            </table>
            


          </div>
        </div>

        <div class="card mb-4">
          <div class="card-body p-0">

            <table class="table table-bordered mb-0 align-middle text-center">
              <thead style="background:#8bc34a;">
                <tr class="text-white" >
                  <th>Clave</th>
                  <th>Descripción</th>
                  <th>UOM</th>
                  <th>Cantidad<br>Bultos/Rollos</th>
                  <th>Cantidad<br>en piezas</th>
                  <th>Orden Pedido</th>
                  <th>No. Conformidad</th>
                  <th>Lote</th>
                  <th>Factura / Remisión</th>
                  <th>No. F/R</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in form.products" :key="item.id_product">
                  <td>{{ item.code }}</td>
                  <td>{{ item.description }}</td>
                  <td>
                    <select class="form-select form-select-sm"
                            v-model="item.unit_measure">

                      <option value="">Seleccionar</option>

                      <option 
                        v-for="u in unitMeasures" 
                        :key="u.value" 
                        :value="u.value"
                      >
                        {{ u.label }}
                      </option>

                    </select>
                  </td>
                  <td>
                    <label for="">
                      <input type="number" class="form-control form-control-sm" v-model="item.bulk_or_roll_quantity">
                    </label>
                  </td>
                  <td>
                    <label for="">
                      <input type="number" class="form-control form-control-sm" v-model="item.individual_quantity">
                    </label>
                  </td>
                  <td>
                    <label for="">
                      <input type="text" class="form-control form-control-sm" v-model="item.document_number">
                    </label>
                  </td>
                  <td>
                    <label for="">
                      <input type="checkbox" class="form-check-input" v-model="item.non_conformity">
                    </label>
                  </td>

                  <td>
                    <label for="">
                      <input type="text" class="form-control form-control-sm" v-model="item.lot">
                    </label>
                  </td>
                  <td>
                    <select class="form-select form-select-sm">
                      <option>Seleccionar</option>
                      <option v-for="d in documentTypes" :key="d.value" :value="d.value">
                        {{ d.label }}
                      </option>

                    </select>
                  </td>
                  <td>
                    <label for="">
                      <input type="text" class="form-control form-control-sm" v-model="item.number">
                    </label>
                  </td>
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
          <button class="btn btn-success px-4" @click="saveOrder">
            Siguiente
          </button>
        </div>

      </div>
    </AppLayout>
  </template>
