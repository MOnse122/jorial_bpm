<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">INICIA SELECCIONANDO TU ACCION</h2>
        </template>

        <div>
            <div>
                <div>
                    <h3>Recepcion de materia prima</h3>
                    <div>
                        <div>
                            <img src="" alt="Nueva">
                            
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </AuthenticatedLayout>
</template>

<style scoped>

.grid-layout {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;

}
.card {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
@media (max-width: 768px) {
    .grid-layout {
        grid-template-columns: 1fr;
    }
}

.card {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

</style>