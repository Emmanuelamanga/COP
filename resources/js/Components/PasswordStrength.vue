<template>
    <div class="space-y-2">
        <!-- Strength Bar -->
        <div class="flex space-x-1">
            <div
                v-for="level in 4"
                :key="level"
                class="h-1 flex-1 rounded-full transition-colors duration-300"
                :class="getBarColor(level)"
            ></div>
        </div>
        
        <!-- Strength Text and Score -->
        <div class="flex items-center justify-between text-xs">
            <span :class="strengthTextColor" class="font-medium">
                {{ strengthText }}
            </span>
            <span class="text-gray-500">
                {{ score }}/4
            </span>
        </div>
        
        <!-- Requirements Checklist -->
        <div v-if="password" class="space-y-1">
            <div
                v-for="requirement in requirements"
                :key="requirement.label"
                class="flex items-center text-xs"
                :class="requirement.met ? 'text-green-600' : 'text-gray-400'"
            >
                <svg
                    class="h-3 w-3 mr-2 flex-shrink-0"
                    :class="requirement.met ? 'text-green-500' : 'text-gray-300'"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        v-if="requirement.met"
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                    />
                    <circle v-else cx="10" cy="10" r="2" />
                </svg>
                {{ requirement.label }}
            </div>
        </div>
        
        <!-- Additional Security Tips -->
        <div v-if="showTips && score < 3" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-2">
                    <h4 class="text-xs font-medium text-yellow-800">Security Tips</h4>
                    <ul class="mt-1 text-xs text-yellow-700 space-y-1">
                        <li v-for="tip in securityTips" :key="tip">• {{ tip }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    password: {
        type: String,
        default: ''
    },
    minLength: {
        type: Number,
        default: 8
    },
    showTips: {
        type: Boolean,
        default: true
    }
});

// Password strength calculation
const score = computed(() => {
    if (!props.password) return 0;
    
    let score = 0;
    
    // Length requirement
    if (props.password.length >= props.minLength) score++;
    
    // Lowercase and uppercase
    if (/[a-z]/.test(props.password) && /[A-Z]/.test(props.password)) score++;
    
    // Numbers
    if (/\d/.test(props.password)) score++;
    
    // Special characters
    if (/[^A-Za-z0-9]/.test(props.password)) score++;
    
    return score;
});

// Requirements checklist
const requirements = computed(() => [
    {
        label: `At least ${props.minLength} characters`,
        met: props.password.length >= props.minLength
    },
    {
        label: 'Contains uppercase and lowercase letters',
        met: /[a-z]/.test(props.password) && /[A-Z]/.test(props.password)
    },
    {
        label: 'Contains at least one number',
        met: /\d/.test(props.password)
    },
    {
        label: 'Contains special characters (!@#$%^&*)',
        met: /[^A-Za-z0-9]/.test(props.password)
    }
]);

// Strength text and colors
const strengthText = computed(() => {
    if (!props.password) return 'Enter password';
    
    switch (score.value) {
        case 0:
        case 1:
            return 'Weak';
        case 2:
            return 'Fair';
        case 3:
            return 'Good';
        case 4:
            return 'Strong';
        default:
            return 'Enter password';
    }
});

const strengthTextColor = computed(() => {
    switch (score.value) {
        case 0:
        case 1:
            return 'text-red-600';
        case 2:
            return 'text-yellow-600';
        case 3:
            return 'text-blue-600';
        case 4:
            return 'text-green-600';
        default:
            return 'text-gray-400';
    }
});

const getBarColor = (level) => {
    if (score.value >= level) {
        switch (score.value) {
            case 1:
                return 'bg-red-500';
            case 2:
                return 'bg-yellow-500';
            case 3:
                return 'bg-blue-500';
            case 4:
                return 'bg-green-500';
            default:
                return 'bg-gray-200';
        }
    }
    return 'bg-gray-200';
};

// Security tips
const securityTips = computed(() => {
    const tips = [];
    
    if (props.password.length < props.minLength) {
        tips.push(`Use at least ${props.minLength} characters for better security`);
    }
    
    if (!/[A-Z]/.test(props.password)) {
        tips.push('Add uppercase letters (A-Z)');
    }
    
    if (!/[a-z]/.test(props.password)) {
        tips.push('Add lowercase letters (a-z)');
    }
    
    if (!/\d/.test(props.password)) {
        tips.push('Add numbers (0-9)');
    }
    
    if (!/[^A-Za-z0-9]/.test(props.password)) {
        tips.push('Add special characters (!@#$%^&*)');
    }
    
    if (tips.length === 0) {
        tips.push('Consider using a passphrase with multiple words');
        tips.push('Avoid using personal information like names or dates');
    }
    
    return tips.slice(0, 3); // Show max 3 tips
});
</script>