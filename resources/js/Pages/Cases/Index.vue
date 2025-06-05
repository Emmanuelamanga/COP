<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cases Management</h2>
                <Link :href="route('cases.create')" 
                      class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Report New Case
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Case Details
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reporter
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
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
                                    <tr v-for="case_ in cases.data" :key="case_.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ case_.case_type }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ case_.county }} - {{ case_.gender }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ case_.user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ case_.user.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(case_.status)" 
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ case_.status.charAt(0).toUpperCase() + case_.status.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(case_.incident_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('cases.show', case_.id)" 
                                                  class="text-indigo-600 hover:text-indigo-900 mr-4">
                                                View
                                            </Link>
                                            <button v-if="canVerify && case_.status === 'pending'" 
                                                    @click="openVerificationModal(case_)"
                                                    class="text-green-600 hover:text-green-900 mr-4">
                                                Verify
                                            </button>
                                            <button v-if="canApprove && case_.status === 'verified'" 
                                                    @click="openApprovalModal(case_)"
                                                    class="text-blue-600 hover:text-blue-900">
                                                Approve
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <nav class="flex items-center justify-between">
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
                                            Showing {{ cases.from }} to {{ cases.to }} of {{ cases.total }} results
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                            <Link v-for="link in cases.links" :key="link.label" 
                                                  :href="link.url"
                                                  :class="getLinkClass(link)"
                                                  v-html="link.label">
                                            </Link>
                                        </nav>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Modal -->
        <div v-if="showVerificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Verify Case</h3>
                    <form @submit.prevent="submitVerification">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Decision</label>
                            <select v-model="verificationForm.status" required
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Decision</option>
                                <option value="verified">Verify</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea v-model="verificationForm.notes" rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm"
                                      placeholder="Add verification notes..."></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="closeVerificationModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="submit" :disabled="verificationForm.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Approval Modal -->
        <div v-if="showApprovalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Approve Case</h3>
                    <form @submit.prevent="submitApproval">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Decision</label>
                            <select v-model="approvalForm.status" required
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Decision</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea v-model="approvalForm.notes" rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm"
                                      placeholder="Add approval notes..."></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="closeApprovalModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="submit" :disabled="approvalForm.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cases: Object,
    canVerify: Boolean,
    canApprove: Boolean,
});

const showVerificationModal = ref(false);
const showApprovalModal = ref(false);
const selectedCase = ref(null);

const verificationForm = useForm({
    status: '',
    notes: '',
});

const approvalForm = useForm({
    status: '',
    notes: '',
});

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        verified: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getLinkClass = (link) => {
    if (link.active) {
        return 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium';
    }
    if (!link.url) {
        return 'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-default';
    }
    return 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const openVerificationModal = (case_) => {
    selectedCase.value = case_;
    verificationForm.reset();
    showVerificationModal.value = true;
};

const closeVerificationModal = () => {
    showVerificationModal.value = false;
    selectedCase.value = null;
};

const submitVerification = () => {
    verificationForm.patch(route('cases.verify', selectedCase.value.id), {
        onSuccess: () => {
            closeVerificationModal();
        },
    });
};

const openApprovalModal = (case_) => {
    selectedCase.value = case_;
    approvalForm.reset();
    showApprovalModal.value = true;
};

const closeApprovalModal = () => {
    showApprovalModal.value = false;
    selectedCase.value = null;
};

const submitApproval = () => {
    approvalForm.patch(route('cases.approve', selectedCase.value.id), {
        onSuccess: () => {
            closeApprovalModal();
        },
    });
};
</script>