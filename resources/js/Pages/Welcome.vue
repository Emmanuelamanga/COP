<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">Citizen Observatory</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link :href="route('visualization.index')" class="text-gray-700 hover:text-blue-600">
                            Data Visualization
                        </Link>
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" 
                              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-gray-700 hover:text-blue-600">
                                Login
                            </Link>
                            <Link :href="route('register')" 
                                  class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Report and Track Community Issues
                </h2>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    A platform for reporting Gender-Based Violence, Sexual and Reproductive Health issues, 
                    and Economic Empowerment cases. Together, we can create safer communities.
                </p>
                
                <div class="grid md:grid-cols-3 gap-8 mt-12">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-red-500 text-3xl mb-4">⚠️</div>
                        <h3 class="text-lg font-semibold mb-2">Gender-Based Violence</h3>
                        <p class="text-gray-600">Report incidents and get support for GBV cases in your community.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-500 text-3xl mb-4">🏥</div>
                        <h3 class="text-lg font-semibold mb-2">Sexual & Reproductive Health</h3>
                        <p class="text-gray-600">Access to healthcare services and reproductive rights issues.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-green-500 text-3xl mb-4">💼</div>
                        <h3 class="text-lg font-semibold mb-2">Economic Empowerment</h3>
                        <p class="text-gray-600">Report economic discrimination and empowerment challenges.</p>
                    </div>
                </div>

                <div class="mt-12">
                    <Link v-if="$page.props.auth.user" :href="route('cases.create')" 
                          class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 mr-4">
                        Report a Case
                    </Link>
                    <Link :href="route('visualization.index')" 
                          class="bg-gray-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-700">
                        View Data
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>