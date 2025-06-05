<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">System Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- System Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">System Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(value, key) in systemInfo" :key="key" 
                             class="border rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                                {{ formatKey(key) }}
                            </div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                {{ value }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Database Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="(value, key) in databaseInfo" :key="key" 
                             class="border rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                                {{ formatKey(key) }}
                            </div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                {{ value }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cache Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Cache Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="(value, key) in cacheInfo" :key="key" 
                             class="border rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                                {{ formatKey(key) }}
                            </div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                {{ value }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">System Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        <button @click="clearCache" :disabled="processing.clearCache"
                                class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center">
                            <span v-if="processing.clearCache" class="mr-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                            Clear Cache
                        </button>

                        <button @click="optimizeSystem" :disabled="processing.optimize"
                                class="bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center justify-center">
                            <span v-if="processing.optimize" class="mr-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                            Optimize System
                        </button>

                        <button @click="backupDatabase" :disabled="processing.backup"
                                class="bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 disabled:opacity-50 flex items-center justify-center">
                            <span v-if="processing.backup" class="mr-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                            Backup Database
                        </button>

                        <button @click="toggleMaintenance" :disabled="processing.maintenance"
                                :class="maintenanceMode ? 'bg-red-600 hover:bg-red-700' : 'bg-yellow-600 hover:bg-yellow-700'"
                                class="text-white px-4 py-3 rounded-lg disabled:opacity-50 flex items-center justify-center">
                            <span v-if="processing.maintenance" class="mr-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                            {{ maintenanceMode ? 'Disable' : 'Enable' }} Maintenance
                        </button>

                    </div>
                </div>

                <!-- System Logs -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent System Activity</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div>
                                <div class="font-medium">System cache cleared</div>
                                <div class="text-sm text-gray-600">Performance optimization completed</div>
                            </div>
                            <span class="text-sm text-gray-500">2 hours ago</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div>
                                <div class="font-medium">Database backup created</div>
                                <div class="text-sm text-gray-600">Automated daily backup</div>
                            </div>
                            <span class="text-sm text-gray-500">1 day ago</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div>
                                <div class="font-medium">System updated</div>
                                <div class="text-sm text-gray-600">Security patches applied</div>
                            </div>
                            <span class="text-sm text-gray-500">3 days ago</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    systemInfo: Object,
    databaseInfo: Object,
    cacheInfo: Object,
});

const processing = ref({
    clearCache: false,
    optimize: false,
    backup: false,
    maintenance: false,
});

const maintenanceMode = ref(false);

const formatKey = (key) => {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const clearCache = () => {
    processing.value.clearCache = true;
    router.post(route('admin.settings.clear-cache'), {}, {
        onFinish: () => {
            processing.value.clearCache = false;
        }
    });
};

const optimizeSystem = () => {
    processing.value.optimize = true;
    router.post(route('admin.settings.optimize'), {}, {
        onFinish: () => {
            processing.value.optimize = false;
        }
    });
};

const backupDatabase = () => {
    processing.value.backup = true;
    router.post(route('admin.settings.backup'), {}, {
        onFinish: () => {
            processing.value.backup = false;
        }
    });
};

const toggleMaintenance = () => {
    processing.value.maintenance = true;
    router.post(route('admin.settings.maintenance'), {
        enable: !maintenanceMode.value
    }, {
        onSuccess: () => {
            maintenanceMode.value = !maintenanceMode.value;
        },
        onFinish: () => {
            processing.value.maintenance = false;
        }
    });
};
</script>