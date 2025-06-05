<template>
    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
                <div class="flex space-x-2">
                    <Link :href="route('admin.analytics')" 
                          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Advanced Analytics
                    </Link>
                    <Link :href="route('admin.export')" 
                          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Export Data
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Key Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <StatCard 
                        title="Total Users" 
                        :value="stats.total_users"
                        :change="calculateChange(stats.total_users, stats.active_users)"
                        icon="👥"
                        color="blue"
                    />
                    <StatCard 
                        title="Total Cases" 
                        :value="stats.total_cases"
                        :change="calculateMonthlyChange(stats.cases_this_month, stats.cases_last_month)"
                        icon="📋"
                        color="green"
                    />
                    <StatCard 
                        title="Pending Cases" 
                        :value="stats.pending_cases"
                        :urgent="stats.pending_cases > 10"
                        icon="⏳"
                        color="yellow"
                    />
                    <StatCard 
                        title="Processing Time" 
                        :value="avgProcessingTime + ' days'"
                        icon="⚡"
                        color="purple"
                    />
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Case Status Distribution -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Case Status Distribution</h3>
                        <Doughnut :data="caseStatusData" :options="chartOptions" />
                    </div>

                    <!-- User Role Distribution -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">User Role Distribution</h3>
                        <Pie :data="userRoleData" :options="chartOptions" />
                    </div>

                </div>

                <!-- Cases Over Time -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Cases Over Time</h3>
                    <Line :data="casesTimelineData" :options="lineChartOptions" />
                </div>

                <!-- Top Counties and Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Top Counties -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Top Counties by Cases</h3>
                        <div class="space-y-3">
                            <div v-for="county in topCounties" :key="county.county" 
                                 class="flex justify-between items-center">
                                <span class="font-medium">{{ county.county }}</span>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                                    {{ county.count }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Recent Cases</h3>
                        <div class="space-y-3">
                            <div v-for="case_ in recentCases" :key="case_.id" 
                                 class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <div>
                                    <div class="font-medium">{{ case_.case_type }}</div>
                                    <div class="text-sm text-gray-600">{{ case_.county }}</div>
                                </div>
                                <span :class="getStatusClass(case_.status)" 
                                      class="px-2 py-1 rounded-full text-xs">
                                    {{ case_.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- System Health -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">System Health</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">98.5%</div>
                            <div class="text-sm text-gray-600">Uptime</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ stats.active_users }}</div>
                            <div class="text-sm text-gray-600">Active Users</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">15ms</div>
                            <div class="text-sm text-gray-600">Avg Response Time</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Doughnut, Pie, Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    stats: Object,
    usersByRole: Object,
    casesOverTime: Object,
    topCounties: Array,
    recentCases: Array,
    avgProcessingTime: Number,
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

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};

const caseStatusData = computed(() => ({
    labels: ['Pending', 'Verified', 'Approved', 'Rejected'],
    datasets: [{
        data: [
            props.stats.pending_cases,
            props.stats.verified_cases,
            props.stats.approved_cases,
            props.stats.rejected_cases,
        ],
        backgroundColor: ['#FCD34D', '#60A5FA', '#34D399', '#F87171'],
    }],
}));

const userRoleData = computed(() => ({
    labels: Object.keys(props.usersByRole),
    datasets: [{
        data: Object.values(props.usersByRole),
        backgroundColor: ['#8B5CF6', '#06B6D4', '#10B981', '#F59E0B'],
    }],
}));

const casesTimelineData = computed(() => {
    const months = Object.keys(props.casesOverTime);
    const datasets = [];
    const statuses = ['pending', 'verified', 'approved', 'rejected'];
    const colors = {
        pending: '#FCD34D',
        verified: '#60A5FA',
        approved: '#34D399',
        rejected: '#F87171'
    };

    statuses.forEach(status => {
        datasets.push({
            label: status.charAt(0).toUpperCase() + status.slice(1),
            data: months.map(month => {
                const monthData = props.casesOverTime[month] || [];
                const statusData = monthData.find(item => item.status === status);
                return statusData ? statusData.count : 0;
            }),
            borderColor: colors[status],
            backgroundColor: colors[status] + '20',
            tension: 0.1,
        });
    });

    return {
        labels: months,
        datasets: datasets,
    };
});

const calculateChange = (current, comparison) => {
    if (!comparison || comparison === 0) return 0;
    return ((current - comparison) / comparison * 100).toFixed(1);
};

const calculateMonthlyChange = (current, previous) => {
    if (!previous || previous === 0) return current > 0 ? 100 : 0;
    return ((current - previous) / previous * 100).toFixed(1);
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        verified: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<style scoped>
canvas {
    height: 300px !important;
}
</style>