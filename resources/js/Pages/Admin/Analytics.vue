<template>
    <AdminLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center py-6">
                        <h1 class="text-2xl font-semibold text-gray-900">Analytics Dashboard</h1>
                        <div class="flex items-center gap-4">
                            <!-- Date Range Selector -->
                            <select v-model="dateRange" @change="fetchAnalytics"
                                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="7">Last 7 days</option>
                                <option value="30">Last 30 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="365">Last year</option>
                            </select>
                            <button @click="exportReport"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Export Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Cases</p>
                                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ analytics.totalCases }}</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <span :class="analytics.casesGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ analytics.casesGrowth >= 0 ? '+' : '' }}{{ analytics.casesGrowth }}%
                                    </span>
                                    vs last period
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Cases</p>
                                <p class="text-2xl font-semibold text-yellow-600 mt-1">{{ analytics.pendingCases }}</p>
                                <p class="text-sm text-gray-500 mt-1">Awaiting verification</p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Approved Cases</p>
                                <p class="text-2xl font-semibold text-green-600 mt-1">{{ analytics.approvedCases }}</p>
                                <p class="text-sm text-gray-500 mt-1">Successfully processed</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Approval Rate</p>
                                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ analytics.approvalRate }}%</p>
                                <p class="text-sm text-gray-500 mt-1">Of verified cases</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Cases by Status Chart -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Cases by Status</h2>
                        <canvas ref="statusChart" height="300"></canvas>
                    </div>

                    <!-- Cases by Type Chart -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Cases by Type</h2>
                        <canvas ref="typeChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Cases Over Time Chart -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Cases Over Time</h2>
                    <canvas ref="timeChart" height="100"></canvas>
                </div>

                <!-- Charts Row 2 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Cases by County -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Counties by Cases</h2>
                        <canvas ref="countyChart" height="300"></canvas>
                    </div>

                    <!-- Gender Distribution -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Gender Distribution</h2>
                        <canvas ref="genderChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Average Processing Time</h3>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Verification Time</span>
                                    <span class="font-medium">{{ analytics.avgVerificationTime }} days</span>
                                </div>
                                <div class="mt-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        :style="`width: ${Math.min(analytics.avgVerificationTime * 10, 100)}%`"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Approval Time</span>
                                    <span class="font-medium">{{ analytics.avgApprovalTime }} days</span>
                                </div>
                                <div class="mt-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full"
                                        :style="`width: ${Math.min(analytics.avgApprovalTime * 10, 100)}%`"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Total Processing</span>
                                    <span class="font-medium">{{ analytics.avgTotalTime }} days</span>
                                </div>
                                <div class="mt-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full"
                                        :style="`width: ${Math.min(analytics.avgTotalTime * 5, 100)}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">User Activity</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Active Submitters</span>
                                <span class="text-lg font-medium">{{ analytics.activeSubmitters }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Active Verifiers</span>
                                <span class="text-lg font-medium">{{ analytics.activeVerifiers }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Active Approvers</span>
                                <span class="text-lg font-medium">{{ analytics.activeApprovers }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                        <div class="space-y-2">
                            <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start gap-3">
                                <div :class="getActivityIconClass(activity.type)"
                                    class="p-1 rounded-full flex-shrink-0">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 truncate">{{ activity.description }}</p>
                                    <p class="text-xs text-gray-500">{{ formatTimeAgo(activity.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'
import AdminLayout from '@/Layouts/AdminLayout.vue';


export default {
    components: {
        AdminLayout
    },
    props: {
        analytics: {
            type: Object,
            default: () => ({
                totalCases: 0,
                pendingCases: 0,
                approvedCases: 0,
                approvalRate: 0,
                casesGrowth: 0,
                avgVerificationTime: 0,
                avgApprovalTime: 0,
                avgTotalTime: 0,
                activeSubmitters: 0,
                activeVerifiers: 0,
                activeApprovers: 0
            })
        },
        chartData: {
            type: Object,
            default: () => ({})
        },
        recentActivities: {
            type: Array,
            default: () => []
        }
    },
    setup(props) {
        const dateRange = ref('30')
        const charts = ref({})

        // Chart refs
        const statusChart = ref(null)
        const typeChart = ref(null)
        const timeChart = ref(null)
        const countyChart = ref(null)
        const genderChart = ref(null)

        const initCharts = async () => {
            await nextTick()

            // Status Chart (Doughnut)
            if (statusChart.value) {
                charts.value.status = new Chart(statusChart.value, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending', 'Verified', 'Approved', 'Rejected'],
                        datasets: [{
                            data: props.chartData.statusData || [0, 0, 0, 0],
                            backgroundColor: ['#FCD34D', '#60A5FA', '#34D399', '#F87171'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                })
            }

            // Type Chart (Bar)
            if (typeChart.value) {
                charts.value.type = new Chart(typeChart.value, {
                    type: 'bar',
                    data: {
                        labels: ['GBV', 'SRH', 'Economic Empowerment'],
                        datasets: [{
                            label: 'Cases',
                            data: props.chartData.typeData || [0, 0, 0],
                            backgroundColor: ['#818CF8', '#34D399', '#FBBF24'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                })
            }

            // Time Chart (Line)
            if (timeChart.value) {
                charts.value.time = new Chart(timeChart.value, {
                    type: 'line',
                    data: {
                        labels: props.chartData.timeLabels || [],
                        datasets: [{
                            label: 'New Cases',
                            data: props.chartData.timeData || [],
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                })
            }

            // County Chart (Horizontal Bar)
            if (countyChart.value) {
                charts.value.county = new Chart(countyChart.value, {
                    type: 'bar',
                    data: {
                        labels: props.chartData.countyLabels || [],
                        datasets: [{
                            label: 'Cases',
                            data: props.chartData.countyData || [],
                            backgroundColor: '#8B5CF6',
                            borderWidth: 0
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                })
            }

            // Gender Chart (Pie)
            if (genderChart.value) {
                charts.value.gender = new Chart(genderChart.value, {
                    type: 'pie',
                    data: {
                        labels: ['Male', 'Female', 'Others'],
                        datasets: [{
                            data: props.chartData.genderData || [0, 0, 0],
                            backgroundColor: ['#3B82F6', '#EC4899', '#6B7280'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                })
            }
        }

        const fetchAnalytics = () => {
            router.get(route('admin.analytics'), {
                range: dateRange.value
            }, {
                preserveState: true,
                preserveScroll: true
            })
        }

        const exportReport = () => {
            window.location.href = route('admin.analytics.export', { range: dateRange.value })
        }

        const getActivityIconClass = (type) => {
            const classes = {
                'case_created': 'bg-blue-100 text-blue-600',
                'case_verified': 'bg-green-100 text-green-600',
                'case_approved': 'bg-purple-100 text-purple-600',
                'case_rejected': 'bg-red-100 text-red-600'
            }
            return classes[type] || 'bg-gray-100 text-gray-600'
        }

        const formatTimeAgo = (date) => {
            const seconds = Math.floor((new Date() - new Date(date)) / 1000)
            const intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60
            }

            for (const [unit, secondsInUnit] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInUnit)
                if (interval >= 1) {
                    return `${interval} ${unit}${interval > 1 ? 's' : ''} ago`
                }
            }
            return 'Just now'
        }

        onMounted(() => {
            initCharts()
        })

        onUnmounted(() => {
            // Destroy all charts
            Object.values(charts.value).forEach(chart => {
                if (chart) chart.destroy()
            })
        })

        return {
            dateRange,
            statusChart,
            typeChart,
            timeChart,
            countyChart,
            genderChart,
            fetchAnalytics,
            exportReport,
            getActivityIconClass,
            formatTimeAgo
        }
    }
}
</script>

<style scoped>
canvas {
    max-height: 300px;
}
</style>