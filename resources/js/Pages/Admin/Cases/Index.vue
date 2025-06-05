<template>
  <AdminLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-6">
            <h1 class="text-2xl font-semibold text-gray-900">Cases Management</h1>
            <Link :href="route('admin.cases.create')"
              class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
            Create New Case
            </Link>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <input v-model="filters.search" @input="debounceSearch" type="text" placeholder="Search cases..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="filters.status" @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="verified">Verified</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>

            <!-- Case Type Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Case Type</label>
              <select v-model="filters.case_type" @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="GBV">GBV</option>
                <option value="SRH">SRH</option>
                <option value="Economic Empowerment">Economic Empowerment</option>
              </select>
            </div>

            <!-- Date Range Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
              <select v-model="filters.date_range" @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
              </select>
            </div>
          </div>

          <!-- Clear Filters -->
          <div class="mt-4 flex justify-end">
            <button @click="clearFilters" class="text-sm text-gray-600 hover:text-gray-900">
              Clear all filters
            </button>
          </div>
        </div>

        <!-- Bulk Actions (Admin Only) -->
        <div v-if="$page.props.auth.user.is_admin && selectedCases.length > 0"
          class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <div class="flex items-center justify-between">
            <span class="text-sm text-blue-800">
              {{ selectedCases.length }} case(s) selected
            </span>
            <div class="flex gap-2">
              <button @click="bulkAction('verify')"
                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                Bulk Verify
              </button>
              <button @click="bulkAction('approve')"
                class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Bulk Approve
              </button>
              <button @click="bulkAction('reject')"
                class="px-3 py-1 bg-orange-600 text-white text-sm rounded hover:bg-orange-700">
                Bulk Reject
              </button>
              <button @click="bulkAction('delete')"
                class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                Bulk Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Cases Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th v-if="$page.props.auth.user.is_admin" class="px-6 py-3 text-left">
                    <input type="checkbox" @change="toggleAllCases" :checked="allCasesSelected"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Case ID
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    County
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Gender
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Submitted By
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="caseItem in cases?.data" :key="caseItem.id" class="hover:bg-gray-50">
                  <td v-if="$page.props.auth.user.is_admin" class="px-6 py-4">
                    <input type="checkbox" :value="caseItem.id" v-model="selectedCases"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    #{{ caseItem.id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ caseItem.county }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100">
                      {{ caseItem.case_type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ caseItem.gender }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(caseItem.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                      {{ caseItem.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ caseItem.user?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(caseItem.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex gap-2">
                      <Link :href="route('cases.show', caseItem.id)" class="text-blue-600 hover:text-blue-900">
                      View
                      </Link>
                      <Link v-if="canEdit(caseItem)" :href="route('cases.edit', caseItem.id)"
                        class="text-green-600 hover:text-green-900">
                      Edit
                      </Link>
                      <button v-if="canDelete(caseItem)" @click="deleteCase(caseItem.id)"
                        class="text-red-600 hover:text-red-900">
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="cases?.data?.length === 0" class="text-center py-12">
            <p class="text-gray-500">No cases found.</p>
          </div>

          <!-- Pagination -->
          <div v-if="cases?.links?.length > 3" class="bg-gray-50 px-6 py-3 flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link v-if="cases.prev_page_url" :href="cases.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
              Previous
              </Link>
              <Link v-if="cases.next_page_url" :href="cases.next_page_url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
              Next
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing <span class="font-medium">{{ cases.from }}</span> to
                  <span class="font-medium">{{ cases.to }}</span> of
                  <span class="font-medium">{{ cases.total }}</span> results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <template v-for="(link, index) in cases.links" :key="index">
                    <Link v-if="link.url" :href="link.url" :class="[
                      link.active
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    ]" v-html="link.label" />
                    <span v-else :class="[
                      'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300'
                    ]" v-html="link.label" />
                  </template>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <!-- Export Button -->
        <div class="mt-6 flex justify-end">
          <button @click="exportCases"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
            Export Cases
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>

</template>

<script>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
  components: {
    Link,
    AdminLayout
  },
  props: {
    cases: Object,
    canVerify: Boolean,
    canApprove: Boolean,
    filters: Object
  },
  setup(props) {
    const page = usePage()
    const selectedCases = ref([])
    const filters = ref({
      search: props.filters?.search || '',
      status: props.filters?.status || '',
      case_type: props.filters?.case_type || '',
      date_range: props.filters?.date_range || ''
    })

    let searchTimeout = null

    const allCasesSelected = computed(() => {
      return props.cases?.data?.length > 0 &&
        selectedCases.value.length === props.cases?.data?.length
    })

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        applyFilters()
      }, 300)
    }

    const applyFilters = () => {
      router.get(route('cases.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
      })
    }

    const clearFilters = () => {
      filters.value = {
        search: '',
        status: '',
        case_type: '',
        date_range: ''
      }
      applyFilters()
    }

    const toggleAllCases = (event) => {
      if (event.target.checked) {
        selectedCases.value = props.cases?.data?.map(c => c.id)
      } else {
        selectedCases.value = []
      }
    }

    const bulkAction = (action) => {
      if (!confirm(`Are you sure you want to ${action} ${selectedCases.value.length} case(s)?`)) {
        return
      }

      router.post(route('cases.bulk-action'), {
        action: action,
        case_ids: selectedCases.value,
        notes: ''
      }, {
        onSuccess: () => {
          selectedCases.value = []
        }
      })
    }

    const deleteCase = (id) => {
      if (confirm('Are you sure you want to delete this case?')) {
        router.delete(route('cases.destroy', id))
      }
    }

    const canEdit = (caseItem) => {
      const user = page.props.auth.user
      return user.is_admin || (caseItem.user_id === user.id && caseItem.status !== 'approved')
    }

    const canDelete = (caseItem) => {
      const user = page.props.auth.user
      return user.is_admin || caseItem.user_id === user.id
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        verified: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    const exportCases = () => {
      const params = new URLSearchParams(filters.value)
      window.location.href = route('cases.export') + '?' + params.toString()
    }

    return {
      selectedCases,
      filters,
      allCasesSelected,
      debounceSearch,
      applyFilters,
      clearFilters,
      toggleAllCases,
      bulkAction,
      deleteCase,
      canEdit,
      canDelete,
      getStatusClass,
      formatDate,
      exportCases
    }
  }
}
</script>