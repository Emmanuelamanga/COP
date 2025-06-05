<template>
    <div :class="[cardClasses, borderClasses]" 
         class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
        
        <!-- Card Header -->
        <div class="p-5">
            <div class="flex items-center">
                
                <!-- Icon Section -->
                <div class="flex-shrink-0">
                    <!-- Custom Icon (Emoji or Text) -->
                    <div v-if="icon" class="text-2xl" :title="title">
                        {{ icon }}
                    </div>
                    
                    <!-- SVG Icon with Background -->
                    <div v-else-if="svgIcon" :class="iconBgClasses" 
                         class="h-10 w-10 rounded-full flex items-center justify-center">
                        <component :is="svgIcon" class="h-6 w-6 text-white" />
                    </div>
                    
                    <!-- Default Icon -->
                    <div v-else :class="iconBgClasses" 
                         class="h-10 w-10 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                
                <!-- Content Section -->
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <!-- Title -->
                        <dt class="text-sm font-medium text-gray-500 truncate" :title="title">
                            {{ title }}
                        </dt>
                        
                        <!-- Main Value and Change Indicator -->
                        <dd class="flex items-baseline">
                            <!-- Main Value -->
                            <div class="text-2xl font-semibold text-gray-900">
                                {{ formattedValue }}
                            </div>
                            
                            <!-- Change Indicator -->
                            <div v-if="showChange" :class="changeColorClasses" 
                                 class="ml-2 flex items-baseline text-sm font-semibold">
                                
                                <!-- Up Arrow -->
                                <svg v-if="numericChange > 0" 
                                     class="self-center flex-shrink-0 h-4 w-4 mr-1" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                
                                <!-- Down Arrow -->
                                <svg v-else-if="numericChange < 0" 
                                     class="self-center flex-shrink-0 h-4 w-4 mr-1" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                
                                <!-- No Change -->
                                <svg v-else class="self-center flex-shrink-0 h-4 w-4 mr-1 text-gray-400" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                </svg>
                                
                                {{ formattedChange }}
                            </div>
                        </dd>
                        
                        <!-- Subtitle -->
                        <dd v-if="subtitle" class="text-xs text-gray-400 mt-1">
                            {{ subtitle }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Status Bar (Optional) -->
        <div v-if="urgent || warning || info" :class="statusBarClasses" 
             class="px-5 py-3 border-t border-gray-100">
            <div class="flex items-center text-sm">
                
                <!-- Status Icon -->
                <svg v-if="urgent" class="h-4 w-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                
                <svg v-else-if="warning" class="h-4 w-4 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                
                <svg v-else-if="info" class="h-4 w-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                
                <!-- Status Message -->
                <span :class="statusTextClasses">
                    {{ statusMessage }}
                </span>
            </div>
        </div>
        
        <!-- Progress Bar (Optional) -->
        <div v-if="showProgress" class="px-5 pb-3">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span>{{ progressLabel }}</span>
                <span>{{ Math.round(progressPercentage) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div :class="progressBarClasses" 
                     class="h-2 rounded-full transition-all duration-300" 
                     :style="{ width: progressPercentage + '%' }">
                </div>
            </div>
        </div>
        
        <!-- Action Button (Optional) -->
        <div v-if="actionLabel && actionHref" class="px-5 pb-4">
            <Link :href="actionHref" 
                  :class="actionButtonClasses"
                  class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200">
                {{ actionLabel }}
                <svg class="ml-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    // Content
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    },
    
    // Change indicator
    change: {
        type: [String, Number],
        default: null
    },
    changeType: {
        type: String,
        default: 'percentage', // 'percentage', 'absolute', 'custom'
        validator: value => ['percentage', 'absolute', 'custom'].includes(value)
    },
    
    // Visual styling
    color: {
        type: String,
        default: 'blue',
        validator: value => ['blue', 'green', 'yellow', 'red', 'purple', 'indigo', 'pink', 'gray', 'emerald', 'orange'].includes(value)
    },
    size: {
        type: String,
        default: 'normal', // 'small', 'normal', 'large'
        validator: value => ['small', 'normal', 'large'].includes(value)
    },
    
    // Icons
    icon: {
        type: String,
        default: ''
    },
    svgIcon: {
        type: Object,
        default: null
    },
    
    // Status indicators
    urgent: {
        type: Boolean,
        default: false
    },
    warning: {
        type: Boolean,
        default: false
    },
    info: {
        type: Boolean,
        default: false
    },
    statusMessage: {
        type: String,
        default: ''
    },
    
    // Progress bar
    showProgress: {
        type: Boolean,
        default: false
    },
    progressValue: {
        type: Number,
        default: 0
    },
    progressMax: {
        type: Number,
        default: 100
    },
    progressLabel: {
        type: String,
        default: 'Progress'
    },
    
    // Action button
    actionLabel: {
        type: String,
        default: ''
    },
    actionHref: {
        type: String,
        default: ''
    },
    
    // Formatting
    formatValue: {
        type: Boolean,
        default: true
    },
    valuePrefix: {
        type: String,
        default: ''
    },
    valueSuffix: {
        type: String,
        default: ''
    }
});

// Computed properties
const numericChange = computed(() => {
    if (props.change === null || props.change === undefined) return 0;
    return parseFloat(props.change) || 0;
});

const showChange = computed(() => {
    return props.change !== null && props.change !== undefined;
});

const formattedValue = computed(() => {
    let val = props.value;
    
    if (props.formatValue && typeof val === 'number') {
        // Add thousand separators for large numbers
        if (val >= 1000) {
            val = val.toLocaleString();
        }
    }
    
    return `${props.valuePrefix}${val}${props.valueSuffix}`;
});

const formattedChange = computed(() => {
    if (!showChange.value) return '';
    
    const absChange = Math.abs(numericChange.value);
    
    switch (props.changeType) {
        case 'percentage':
            return `${absChange}%`;
        case 'absolute':
            return absChange.toLocaleString();
        case 'custom':
            return props.change.toString();
        default:
            return `${absChange}%`;
    }
});

const progressPercentage = computed(() => {
    if (!props.showProgress) return 0;
    return Math.min(100, Math.max(0, (props.progressValue / props.progressMax) * 100));
});

// Style classes
const cardClasses = computed(() => {
    const sizeClasses = {
        small: 'text-sm',
        normal: '',
        large: 'text-lg'
    };
    return sizeClasses[props.size] || '';
});

const borderClasses = computed(() => {
    if (props.urgent) return 'border-l-4 border-red-400';
    if (props.warning) return 'border-l-4 border-yellow-400';
    if (props.info) return 'border-l-4 border-blue-400';
    return '';
});

const iconBgClasses = computed(() => {
    const colorClasses = {
        blue: 'bg-blue-500',
        green: 'bg-green-500',
        yellow: 'bg-yellow-500',
        red: 'bg-red-500',
        purple: 'bg-purple-500',
        indigo: 'bg-indigo-500',
        pink: 'bg-pink-500',
        gray: 'bg-gray-500',
        emerald: 'bg-emerald-500',
        orange: 'bg-orange-500'
    };
    return colorClasses[props.color] || colorClasses.blue;
});

const changeColorClasses = computed(() => {
    if (!showChange.value) return '';
    
    if (numericChange.value > 0) {
        return 'text-green-600';
    } else if (numericChange.value < 0) {
        return 'text-red-600';
    } else {
        return 'text-gray-400';
    }
});

const statusBarClasses = computed(() => {
    if (props.urgent) return 'bg-red-50';
    if (props.warning) return 'bg-yellow-50';
    if (props.info) return 'bg-blue-50';
    return 'bg-gray-50';
});

const statusTextClasses = computed(() => {
    if (props.urgent) return 'text-red-700';
    if (props.warning) return 'text-yellow-700';
    if (props.info) return 'text-blue-700';
    return 'text-gray-700';
});

const progressBarClasses = computed(() => {
    const colorClasses = {
        blue: 'bg-blue-500',
        green: 'bg-green-500',
        yellow: 'bg-yellow-500',
        red: 'bg-red-500',
        purple: 'bg-purple-500',
        indigo: 'bg-indigo-500',
        pink: 'bg-pink-500',
        gray: 'bg-gray-500',
        emerald: 'bg-emerald-500',
        orange: 'bg-orange-500'
    };
    return colorClasses[props.color] || colorClasses.blue;
});

const actionButtonClasses = computed(() => {
    const colorClasses = {
        blue: 'bg-blue-100 text-blue-700 hover:bg-blue-200',
        green: 'bg-green-100 text-green-700 hover:bg-green-200',
        yellow: 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200',
        red: 'bg-red-100 text-red-700 hover:bg-red-200',
        purple: 'bg-purple-100 text-purple-700 hover:bg-purple-200',
        indigo: 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200',
        pink: 'bg-pink-100 text-pink-700 hover:bg-pink-200',
        gray: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
        emerald: 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200',
        orange: 'bg-orange-100 text-orange-700 hover:bg-orange-200'
    };
    return colorClasses[props.color] || colorClasses.blue;
});
</script>