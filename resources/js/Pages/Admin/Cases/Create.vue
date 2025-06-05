<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Report a Case</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">County</label>
                                <input
                                    v-model="form.county"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="$page.props.errors.county" class="text-red-600 text-sm mt-1">
                                    {{ $page.props.errors.county }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Case Type</label>
                                <select
                                    v-model="form.case_type"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Case Type</option>
                                    <option value="GBV">Gender-Based Violence</option>
                                    <option value="SRH">Sexual & Reproductive Health</option>
                                    <option value="Economic Empowerment">Economic Empowerment</option>
                                </select>
                                <div v-if="$page.props.errors.case_type" class="text-red-600 text-sm mt-1">
                                    {{ $page.props.errors.case_type }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Gender</label>
                                <select
                                    v-model="form.gender"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Others">Others</option>
                                </select>
                                <div v-if="$page.props.errors.gender" class="text-red-600 text-sm mt-1">
                                    {{ $page.props.errors.gender }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Incident Date</label>
                                <input
                                    v-model="form.incident_date"
                                    type="date"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="$page.props.errors.incident_date" class="text-red-600 text-sm mt-1">
                                    {{ $page.props.errors.incident_date }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea
                                    v-model="form.description"
                                    required
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Please provide details about the case..."
                                ></textarea>
                                <div v-if="$page.props.errors.description" class="text-red-600 text-sm mt-1">
                                    {{ $page.props.errors.description }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact Information</label>
                                <input
                                    v-model="form.contact_information"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Phone number or email"
                                />
                                <div v-if="$page.props.errors.contact_information" class="text-red-600 text-sm mt-1">
                                    {{ $page.props.errors.contact_information }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4">
                                <Link :href="route('cases.index')" class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                                >
                                    <span v-if="form.processing">Submitting...</span>
                                    <span v-else>Submit Case</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';



const form = useForm({
    county: '',
    case_type: '',
    gender: '',
    incident_date: '',
    description: '',
    contact_information: '',
});

const submit = () => {
    form.post(route('admin.cases.store'));
};
</script>