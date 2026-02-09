<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="fw-bold fs-4 mb-0">
        Inicia seleccionando una acción para comenzar a trabajar
      </h2>
    </template>

    <div >
      <div class="container">
        <div class="row justify-content-center g-5">

          <!-- CARD 1 -->
          <div class="col-md-6 col-lg-5">
            <div class="card action-card text-center">
              <div class="card-body">

                <h5 class="card-title mb-4 fw-bold text-primary-custom">
                  Recepción de <br> materia prima <br>(Entrada)
                </h5>

                <div class="d-flex justify-content-center gap-5">
                  <div class="text-center">
                    <Link href="/purchases" class="btn btn-light" style="border:none; background:transparent; cursor:pointer; text-decoration:none;">
                        <button type="button" style="background:transparent; border:none; padding:0;">
                            <img src="../../../public/images/addDoc.png" class="icon-img mb-2" alt="Add new provider">
                            <span class="text-new">+Nueva</span>
                        </button>
                    </Link>

                  </div>

                  <div class="text-center">
                    <button type="button" class="btn btn-light" style="border:none; background:transparent; cursor:pointer;" onclick="console.log('Consulta clicked')">
                        <img src="../../../public/images/addDoc.png" class="icon-img mb-2" alt="Consulta">
                        <div class="text-consult">*Consulta</div>
                    </button>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- CARD 2 -->
          <div class="col-md-6 col-lg-5">
            <div class="card action-card text-center">
              <div class="card-body">

                <h5 class="card-title mb-4 fw-bold text-primary-custom">
                  Estatus del <br> Producto PCC <br> (Liberación)
                </h5>

                <div class="d-flex justify-content-center gap-5">
                  <div class="text-center">
                    <button type="button" class="btn btn-light" style="border:none; background:transparent; cursor:pointer;" onclick="console.log('Consulta clicked')">

                        <img src="../../../public/images/addDoc.png" class="icon-img mb-2" alt="Nueva">
                        <div class="text-new">+Nueva</div>
                    </button>
                  </div>

                  <div class="text-center">
                    <button type="button" class="btn btn-light" style="border:none; background:transparent; cursor:pointer;" onclick="console.log('Consulta clicked')">

                        <img src="../../../public/images/addDoc.png" class="icon-img mb-2" alt="Consulta">
                        <div class="text-consult">*Consulta</div>
                    </button>
                  </div>
                </div>

              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<style scoped>


/* Card estilo */
.action-card {
  border-radius: 30px;
  border: none;
  padding: 2rem 1rem;
}

/* Color del título */
.text-primary-custom {
  color: #163a5f;
}

/* Iconos */
.icon-img {
  width: 70px;
}

/* Textos */
.text-new {
  color: #7ac943;
  font-weight: bold;
  font-size: 1.1rem;
}

.text-consult {
  color: #1fa0e0;
  font-weight: bold;
  font-size: 1.1rem;
}
</style>
