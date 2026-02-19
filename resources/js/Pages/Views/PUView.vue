<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'





</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-2xl font-bold mb-4">Vista de Orden de Pedido</h1>
        </template>
        <div class="container-fluid py-3 bg-lightgray rounded-3 main-scroll">
<div class="card shadow-sm mb-3 p-3 rounded-3">
    <div class="row g-3 align-items-center">
        <div class="col-md-2">
            <label class="form-label">Proveedor</label>
            <input type="text" class="form-control" placeholder="Proveedor">
        </div>

        <div class="col-md-2">
            <label class="form-label">Número de Orden</label>
            <input type="text" class="form-control" placeholder="Número de Orden">
        </div>

        <div class="col-md-2">
            <label class="form-label">Fecha de Orden</label>
            <input type="date" class="form-control">
        </div>

        <div class="col-md-2">
            <label class="form-label">Fecha de Entrega</label>
            <input type="date" class="form-control">
        </div>
    </div>
</div>

<!-- TABLA -->
            <div class="card shadow-sm p-3 rounded-3">
                <div class="row">
                    <div class="col-12">
                        <label class="form-label">Productos</label>

                        <table class="table table-sm table-bordered text-center align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th width="55%">Dato</th>
                                    <th width="15%">Cumple</th>
                                    <th width="15%">No cumple</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Producto ejemplo</td>
                                    <td><input type="checkbox"></td>
                                    <td><input type="checkbox"></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>