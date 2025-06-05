<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('home')" class="text-xl font-bold text-gray-900">
                            Citizen Observatory
                        </Link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" 
                              class="text-gray-700 hover:text-blue-600">
                            Dashboard
                        </Link>
                        <Link v-else :href="route('login')" class="text-gray-700 hover:text-blue-600">
                            Login
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Data Visualization</h1>
                <p class="text-gray-600">Explore approved case data through interactive charts and graphs.</p>
            </div>

            <!-- Filters -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h3 class="text-lg font-semibold mb-4">Filters</h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">County</label>
                        <select v-model="filters.county" @change="applyFilters" 
                                class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">All Counties</option>
                            <option v-for="county in counties" :key="county" :value="county">
                                {{ county }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Case Type</label>
                        <select v-model="filters.case_type" @change="applyFilters"
                                class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">All Types</option>
                            <option value="GBV">Gender-Based Violence</option>
                            <option value="SRH">Sexual & Reproductive Health</option>
                            <option value="Economic Empowerment">Economic Empowerment</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select v-model="filters.gender" @change="applyFilters"
                                class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input v-model="filters.date_from" @change="applyFilters" type="date"
                               class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input v-model="filters.date_to" @change="applyFilters" type="date"
                               class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button @click="clearFilters" 
                            class="text-sm text-blue-600 hover:text-blue-800">
                        Clear All Filters
                    </button>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-4">Cases by Type</h3>
                    <Doughnut v-if="chartData.casesByType" :data="casesByTypeData" :options="chartOptions" />
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-4">Cases by Gender</h3>
                    <Pie v-if="chartData.casesByGender" :data="casesByGenderData" :options="chartOptions" />
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-4">Top Counties</h3>
                    <Bar v-if="chartData.casesByCounty" :data="casesByCountyData" :options="barChartOptions" />
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-4">Cases Over Time</h3>
                    <Line v-if="chartData.casesOverTime" :data="casesOverTimeData" :options="lineChartOptions" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Doughnut, Pie, Bar, Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    chartData: Object,
    filters: Object,
    counties: Array,
});

const filters = ref({
    county: props.filters.county || '',
    case_type: props.filters.case_type || '',
    gender: props.filters.gender || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
    },
};

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};

const casesByTypeData = computed(() => ({
    labels: Object.keys(props.chartData.casesByType || {}),
    datasets: [{
        data: Object.values(props.chartData.casesByType || {}),
        backgroundColor: [
            '#EF4444', // Red for GBV
            '#3B82F6', // Blue for SRH
            '#10B981', // Green for Economic Empowerment
        ],
    }],
}));

const casesByGenderData = computed(() => ({
    labels: Object.keys(props.chartData.casesByGender || {}),
    datasets: [{
        data: Object.values(props.chartData.casesByGender || {}),
        backgroundColor: [
            '#8B5CF6', // Purple for Male
            '#F59E0B', // Amber for Female
            '#6B7280', // Gray for Others
        ],
    }],
}));

const casesByCountyData = computed(() => ({
    labels: Object.keys(props.chartData.casesByCounty || {}),
    datasets: [{
        label: 'Cases',
        data: Object.values(props.chartData.casesByCounty || {}),
        backgroundColor: '#3B82F6',
    }],
}));

const casesOverTimeData = computed(() => ({
    labels: (props.chartData.casesOverTime || []).map(item => item.date),
    datasets: [{
        label: 'Cases',
        data: (props.chartData.casesOverTime || []).map(item => item.count),
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.1,
    }],
}));

const applyFilters = () => {
    router.get(route('visualization.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filters.value = {
        county: '',
        case_type: '',
        gender: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};
</script>

<style scoped>
canvas {
    height: 300px !important;
}
</style>