
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  evaluation: Object
})
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-dark">
          Registro de muestreo individual
        </h2>
      </div>
    </template>

    <div class="container mt-4">
      <div class="card shadow rounded-4">
        <div class="card-body">

          <!-- Impacto Global -->
          <h5 class="fw-bold mb-3">Impacto global</h5>

          <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
              <tr>
                <th class="text-start">Descripción</th>
                <th>Alto</th>
                <th>Medio</th>
                <th>Bajo</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="text-start bg-warning">
                  1. IMPACTO EN EL TRABAJO
                </td>
                <td><input type="checkbox" /></td>
                <td><input type="checkbox" /></td>
                <td><input type="checkbox" /></td>
              </tr>
              <tr>
                <td class="text-start bg-info text-white">
                  2. IMPACTO EN LIMPIEZA E INOCUIDAD
                </td>
                <td><input type="checkbox" /></td>
                <td><input type="checkbox" /></td>
                <td><input type="checkbox" /></td>
              </tr>
              <tr>
                <td class="text-start bg-success text-white">
                  3. IMPACTO EN ENTREGAS
                </td>
                <td><input type="checkbox" /></td>
                <td><input type="checkbox" /></td>
                <td><input type="checkbox" /></td>
              </tr>
            </tbody>
          </table>

          <hr />

          <!-- Datos de inspección -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nivel de inspección (MIL-STD-105D)</label>
              <input type="text" class="form-control" placeholder="G1" />
            </div>

            <div class="col-md-6">
              <label class="form-label">NAC (AQL)</label>
              <input type="text" class="form-control" placeholder="4.0" />
            </div>
          </div>

          <!-- Puntos de revisión -->
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label">P1. Medidas (ancho y largo)</label>
              <select class="form-select">
                <option>Seleccionar</option>
                <option>Conforme</option>
                <option>No conforme</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">P2. Espesor</label>
              <select class="form-select">
                <option>Seleccionar</option>
                <option>Conforme</option>
                <option>No conforme</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">P3. Sellado y resistencia</label>
              <select class="form-select">
                <option>Seleccionar</option>
                <option>Conforme</option>
                <option>No conforme</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">P4. Desprendimiento de color</label>
              <select class="form-select">
                <option>Seleccionar</option>
                <option>Conforme</option>
                <option>No conforme</option>
              </select>
            </div>
          </div>

          <!-- Resultados -->
          <div class="row mb-4">
            <div class="col-md-4">
              <label class="form-label">Tamaño de muestra</label>
              <input type="number" class="form-control" />
            </div>

            <div class="col-md-4">
              <label class="form-label">Se acepta</label>
              <input type="number" class="form-control" />
            </div>

            <div class="col-md-4">
              <label class="form-label">Se rechaza</label>
              <input type="number" class="form-control" />
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Resultado del lote</label>
            <select class="form-select">
              <option>Seleccionar</option>
              <option>Aprobado</option>
              <option>Rechazado</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label">Disposición del material</label>
            <select class="form-select">
              <option>Seleccionar</option>
              <option>Uso</option>
              <option>Devolución</option>
              <option>Destrucción</option>
            </select>
          </div>

          <!-- Botones -->
          <div class="d-flex justify-content-end gap-3">
            <button class="btn btn-danger">
              + No Conformidad
            </button>
            <button class="btn btn-success">
              Guardar
            </button>
          </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>