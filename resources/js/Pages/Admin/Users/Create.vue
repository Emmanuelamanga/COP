<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('admin.users.index')" 
                          class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New User</h2>
                </div>
                <div class="text-sm text-gray-500">
                    Admin Panel
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Progress Steps -->
                <div class="mb-8">
                    <nav aria-label="Progress">
                        <ol class="flex items-center">
                            <li class="relative pr-8 sm:pr-20">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="h-0.5 w-full bg-gray-200"></div>
                                </div>
                                <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 hover:bg-indigo-900">
                                    <span class="text-white text-sm font-medium">1</span>
                                </div>
                                <span class="absolute top-10 left-0 text-xs font-medium text-gray-500">Basic Info</span>
                            </li>
                            
                            <li class="relative pr-8 sm:pr-20">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="h-0.5 w-full bg-gray-200"></div>
                                </div>
                                <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-gray-300">
                                    <span class="text-gray-600 text-sm font-medium">2</span>
                                </div>
                                <span class="absolute top-10 left-0 text-xs font-medium text-gray-500">Role & Permissions</span>
                            </li>
                            
                            <li class="relative">
                                <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-gray-300">
                                    <span class="text-gray-600 text-sm font-medium">3</span>
                                </div>
                                <span class="absolute top-10 left-0 text-xs font-medium text-gray-500">Review</span>
                            </li>
                        </ol>
                    </nav>
                </div>

                <!-- Main Form Card -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    
                    <!-- Form Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">User Information</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Create a new user account with appropriate role and permissions.
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    New User
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Content -->
                    <form @submit.prevent="submitForm" class="px-6 py-6 space-y-8">
                        
                        <!-- Personal Information Section -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h4 class="text-md font-medium text-gray-900 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Personal Information
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">Basic user details and contact information.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Full Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.name }"
                                            placeholder="Enter full name"
                                        />
                                        <div v-if="form.name" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        This will be displayed throughout the system
                                    </div>
                                </div>

                                <!-- Email Address -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.email }"
                                            placeholder="user@example.com"
                                            @blur="checkEmailAvailability"
                                        />
                                        <div v-if="emailStatus === 'valid'" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div v-else-if="emailStatus === 'checking'" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </div>
                                    <div v-else-if="emailStatus === 'taken'" class="mt-1 text-sm text-red-600">
                                        This email address is already registered
                                    </div>
                                    <div v-else class="mt-1 text-xs text-gray-500">
                                        Used for login and system notifications
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Section -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h4 class="text-md font-medium text-gray-900 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Security & Authentication
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">Set up login credentials and security preferences.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Password -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="password"
                                            v-model="form.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-10"
                                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.password }"
                                            placeholder="Enter secure password"
                                        />
                                        <button
                                            type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        >
                                            <svg v-if="showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                            </svg>
                                            <svg v-else class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.password }}
                                    </div>
                                    <div class="mt-1">
                                        <PasswordStrength :password="form.password" />
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            :type="showPasswordConfirm ? 'text' : 'password'"
                                            required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-10"
                                            :class="{ 
                                                'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.password_confirmation,
                                                'border-green-300 focus:border-green-500 focus:ring-green-500': form.password_confirmation && form.password === form.password_confirmation
                                            }"
                                            placeholder="Confirm password"
                                        />
                                        <button
                                            type="button"
                                            @click="showPasswordConfirm = !showPasswordConfirm"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        >
                                            <svg v-if="showPasswordConfirm" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                            </svg>
                                            <svg v-else class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.password_confirmation }}
                                    </div>
                                    <div v-else-if="form.password_confirmation && form.password !== form.password_confirmation" class="mt-1 text-sm text-red-600">
                                        Passwords do not match
                                    </div>
                                    <div v-else-if="form.password_confirmation && form.password === form.password_confirmation" class="mt-1 text-sm text-green-600">
                                        Passwords match
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role & Permissions Section -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-4">
                                <h4 class="text-md font-medium text-gray-900 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Role & Permissions
                                </h4>
                                <p class="mt-1 text-sm text-gray-500">Define the user's role and system access level.</p>
                            </div>

                            <div class="space-y-6">
                                <!-- Role Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        User Role <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div v-for="role in roles" :key="role.value" 
                                             @click="form.role = role.value"
                                             class="relative cursor-pointer">
                                            <div :class="[
                                                'border-2 rounded-lg p-4 hover:border-indigo-300 transition-colors',
                                                form.role === role.value ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'
                                            ]">
                                                <div class="flex items-center">
                                                    <input
                                                        type="radio"
                                                        :value="role.value"
                                                        v-model="form.role"
                                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                                    />
                                                    <div class="ml-3">
                                                        <div class="flex items-center">
                                                            <span class="text-2xl mr-2">{{ role.icon }}</span>
                                                            <span class="text-sm font-medium text-gray-900">{{ role.label }}</span>
                                                        </div>
                                                        <p class="text-xs text-gray-500 mt-1">{{ role.description }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.role" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.role }}
                                    </div>
                                </div>

                                <!-- Role Permissions Display -->
                                <div v-if="selectedRoleInfo" class="bg-gray-50 rounded-lg p-4">
                                    <h5 class="text-sm font-medium text-gray-900 mb-2">
                                        {{ selectedRoleInfo.label }} Permissions:
                                    </h5>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                        <li v-for="permission in selectedRoleInfo.permissions" :key="permission" 
                                            class="flex items-center">
                                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            {{ permission }}
                                        </li>
                                    </ul>
                                </div>

                                <!-- Account Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Account Status
                                    </label>
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center">
                                            <input
                                                type="checkbox"
                                                v-model="form.is_active"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                            <span class="ml-2 text-sm text-gray-700">
                                                Active account (user can login and access the system)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Inactive users cannot log in but their data is preserved
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <div class="flex items-center space-x-4">
                                <Link :href="route('admin.users.index')" 
                                      class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    Cancel
                                </Link>
                                <button
                                    type="button"
                                    @click="saveAsDraft"
                                    :disabled="form.processing"
                                    class="bg-gray-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 transition-colors"
                                >
                                    Save as Draft
                                </button>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div v-if="form.processing" class="flex items-center text-sm text-gray-500">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Creating user...
                                </div>
                                
                                <button
                                    type="submit"
                                    :disabled="form.processing || !isFormValid"
                                    class="bg-indigo-600 py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <span v-if="form.processing">Creating...</span>
                                    <span v-else>Create User</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Help Panel -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">User Creation Guidelines</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Admins</strong> have full system access including user management</li>
                                    <li><strong>Verifiers</strong> can review and verify submitted cases</li>
                                    <li><strong>Approvers</strong> can approve verified cases for public display</li>
                                    <li><strong>Users</strong> can submit cases and view their own submissions</li>
                                    <li>Email addresses must be unique across the system</li>
                                    <li>Passwords should be at least 8 characters with mixed case and numbers</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PasswordStrength from '@/Components/PasswordStrength.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';

// Form data
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
    is_active: true,
});

// Component state
const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const emailStatus = ref(''); // 'checking', 'valid', 'taken'

// Role definitions
const roles = ref([
    {
        value: 'user',
        label: 'User',
        icon: '👤',
        description: 'Can submit and view own cases',
        permissions: [
            'Submit new cases',
            'View own cases',
            'Update profile',
            'View public statistics'
        ]
    },
    {
        value: 'verifier',
        label: 'Verifier',
        icon: '🔍',
        description: 'Can verify submitted cases',
        permissions: [
            'All User permissions',
            'View pending cases',
            'Verify/reject cases',
            'Add verification notes'
        ]
    },
    {
        value: 'approver',
        label: 'Approver',
        icon: '✅',
        description: 'Can approve verified cases',
        permissions: [
            'All User permissions',
            'View verified cases',
            'Approve/reject cases',
            'Add approval notes',
            'Publish to public dashboard'
        ]
    },
    {
        value: 'admin',
        label: 'Admin',
        icon: '⚡',
        description: 'Full system access and control',
        permissions: [
            'All system permissions',
            'User management',
            'System settings',
            'Data export',
            'Analytics access',
            'System maintenance'
        ]
    }
]);

// Computed properties
const selectedRoleInfo = computed(() => {
    return roles.value.find(role => role.value === form.role);
});

const isFormValid = computed(() => {
    return form.name && 
           form.email && 
           form.password && 
           form.password_confirmation && 
           form.password === form.password_confirmation &&
           form.role &&
           emailStatus.value !== 'taken' &&
           !form.hasErrors;
});

// Methods
const checkEmailAvailability = debounce(async () => {
    if (!form.email || !form.email.includes('@')) return;
    
    emailStatus.value = 'checking';
    
    try {
        // Simulate API call to check email availability
        // In real implementation, you would make an actual API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Mock email validation - replace with actual API call
        const existingEmails = ['admin@example.com', 'test@example.com'];
        if (existingEmails.includes(form.email.toLowerCase())) {
            emailStatus.value = 'taken';
        } else {
            emailStatus.value = 'valid';
        }
    } catch (error) {
        emailStatus.value = '';
    }
}, 500);

const submitForm = () => {
    if (!isFormValid.value) return;
    
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            // Handle success - redirect will be handled by the controller
        },
        onError: (errors) => {
            // Handle validation errors
            console.error('Form errors:', errors);
        }
    });
};

const saveAsDraft = () => {
    // Save form data to localStorage for later completion
    const draftData = {
        name: form.name,
        email: form.email,
        role: form.role,
        is_active: form.is_active,
        timestamp: new Date().toISOString()
    };
    
    localStorage.setItem('user_create_draft', JSON.stringify(draftData));
    
    // Show success message
    alert('Draft saved successfully! You can continue editing later.');
};

const loadDraft = () => {
    const savedDraft = localStorage.getItem('user_create_draft');
    if (savedDraft) {
        try {
            const draftData = JSON.parse(savedDraft);
            form.name = draftData.name || '';
            form.email = draftData.email || '';
            form.role = draftData.role || 'user';
            form.is_active = draftData.is_active !== undefined ? draftData.is_active : true;
        } catch (error) {
            console.error('Error loading draft:', error);
        }
    }
};

// Watchers
watch(() => form.email, () => {
    emailStatus.value = '';
});

// Load draft on component mount
loadDraft();
</script>

<style scoped>
/* Custom animations for form validation */
.border-red-300 {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Loading spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Smooth transitions */
.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Focus ring styles */
.focus\:ring-indigo-500:focus {
    --tw-ring-opacity: 1;
    --tw-ring-color: rgb(99 102 241 / var(--tw-ring-opacity));
}

/* Custom hover effects */
.hover\:shadow-md:hover {
    --tw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color), 0 2px 4px -2px var(--tw-shadow-color);
    box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}
</style>