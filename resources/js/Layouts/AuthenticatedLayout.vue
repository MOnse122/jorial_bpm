<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'


const sidebarOpen = ref(true)

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
}

function logout() {
    router.post(route('logout'))
}
</script>

<template>
    <div class="min-h-screen flex bg-gray-100">

        <!-- SIDEBAR -->
        <aside
            class="bg-white border-r border-gray-200 transition-all duration-300"
            :class="sidebarOpen ? 'w-64' : 'w-16'"
        >
            <!-- HEADER -->
            <div class="h-16 flex items-center justify-between px-4 border-b">
                <span v-if="sidebarOpen" class="font-bold text-lg">
                    Jorial
                </span>
                <button @click="toggleSidebar" class="text-gray-600">
                    <i class="fa-solid fa-thumbtack"></i>
                </button>
            </div>

            <!-- LINKS -->
            <nav class="mt-4">
                <Link
                    href="/dashboard"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 no-underline"
                >
                    <i class="fa-solid fa-house"></i>
                    <span v-if="sidebarOpen" class="ml-3">Dashboard</span>
                </Link>

                <Link
                    href="/providers"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 no-underline"
                >
                    <i class="fa-solid fa-truck-field"></i>
                    <span v-if="sidebarOpen" class="ml-3">Proveedores</span>
                </Link>

                <Link
                    href="/purchases/puview"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 no-underline"
                >
                    <i class="fa-solid fa-diagram-project"></i>
                    <span v-if="sidebarOpen" class="ml-3">Ordenes de Pedido</span>
                </Link>

                <Link
                    href="/check/test"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 no-underline"
                >
                    <i class="fa-solid fa-list-check"></i>
                    <span v-if="sidebarOpen" class="ml-3">Test</span>
                </Link>

                <button
                    @click="logout"
                    class="w-full text-left flex items-center px-4 py-2 text-red-600 hover:bg-red-50 no-underline"
                >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span v-if="sidebarOpen" class="ml-3">Cerrar sesión</span>
                </button>
            </nav>
        </aside>

        <!-- CONTENIDO -->
        <div class="flex-1 flex flex-col">

            <!-- TOP BAR -->
            <header class="h-16 bg-white border-b flex items-center px-6">
                <h2 class="font-semibold text-xl text-gray-800">
                    <slot name="header" />
                </h2>
            </header>

            <main class="flex-1 p-6 w-full">
                <slot />
            </main>

        </div>
    </div>
</template>
