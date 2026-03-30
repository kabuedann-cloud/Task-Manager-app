<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';

const props = defineProps({
    report: Object,
    total: Number
});

const getStatusColor = (status) => {
    const colors = {
        pending: 'text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800',
        in_progress: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30',
        done: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30',
    };
    return colors[status?.toLowerCase()] || 'text-gray-600 bg-gray-100';
};

const getStatusDotColor = (status) => {
    const colors = {
        pending: 'bg-slate-400',
        in_progress: 'bg-blue-500',
        done: 'bg-emerald-500',
    };
    return colors[status?.toLowerCase()] || 'bg-gray-400';
};

const printPage = () => {
    window.print();
};

const completedCount = computed(() => {
    return props.report.data.find(r => r.status === 'done')?.count || 0;
});

const completionRate = computed(() => {
    if (!props.total) return 0;
    return Math.round((completedCount.value / props.total) * 100);
});

const isDarkMode = ref(false);

onMounted(() => {
    isDarkMode.value = localStorage.getItem('theme') === 'dark';
    updateTheme();
});

watch(isDarkMode, (newValue) => {
    localStorage.setItem('theme', newValue ? 'dark' : 'light');
    updateTheme();
});

const updateTheme = () => {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
};
</script>

<template>
    <Head title="Task Report" />

    <div class="min-h-screen bg-slate-100 dark:bg-slate-900 transition-colors duration-300 py-8 px-4 sm:px-6 lg:px-8 print:bg-white print:py-0">
        <div class="max-w-5xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8 flex flex-col items-start gap-5 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800 print:mb-8 print:border-none print:p-0 print:shadow-none md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 flex-shrink-0 rounded-md bg-emerald-600 text-white print:hidden flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="mb-1 text-2xl font-semibold text-slate-900 dark:text-white sm:text-3xl">
                            Task Report
                        </h1>
                        <p class="flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Generated on {{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 w-full md:w-auto mt-2 md:mt-0 no-print">
                    <button @click="toggleTheme" class="flex items-center justify-center rounded-md p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white md:flex-none" title="Toggle Dark Mode">
                        <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    <Link href="/" class="flex flex-1 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-center text-sm font-medium text-slate-700 no-underline transition-colors hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700/50 md:flex-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Back to Tasks
                    </Link>
                    <button @click="printPage" class="flex flex-1 items-center justify-center gap-2 rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 md:flex-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Report
                    </button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <div class="mb-2 flex w-full items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Tasks</h3>
                    </div>
                    <h2 class="pl-1 text-3xl font-semibold text-slate-900 dark:text-white sm:text-4xl">{{ total }}</h2>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <div class="mb-2 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">Completion Rate</h3>
                    </div>
                    <h2 class="flex items-baseline gap-1 pl-1 text-3xl font-semibold text-slate-900 dark:text-white sm:text-4xl">
                        {{ completionRate }}<span class="text-xl font-bold text-slate-400">%</span>
                    </h2>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800 print:border-slate-300">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-900/40">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-3">
                        Status Summary
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-slate-200 bg-white text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Count</th>
                                <th class="px-6 py-4 text-right w-1/3">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-if="report.data.length === 0">
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500 italic text-sm">No task data available for this report.</td>
                            </tr>
                            <tr v-for="item in report.data" :key="item.status" class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-900/40">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div :class="['h-2 w-2 rounded-full', getStatusDotColor(item.status)]"></div>
                                        <span class="text-sm font-medium capitalize text-slate-700 dark:text-slate-200">{{ item.status.replace('_', ' ') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span :class="['inline-block rounded-md px-3 py-1 text-xs font-medium', getStatusColor(item.status)]">
                                        {{ item.count }} {{ item.count === 1 ? 'Task' : 'Tasks' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-3 w-full">
                                        <div class="w-full max-w-[100px] h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden hidden sm:block">
                                            <div :class="['h-full transition-all duration-1000', getStatusDotColor(item.status)]" :style="{ width: `${total ? Math.round(item.count / total * 100) : 0}%` }"></div>
                                        </div>
                                        <span class="font-bold text-sm text-slate-600 dark:text-slate-300 min-w-[3rem] text-right">
                                            {{ total ? Math.round(item.count / total * 100) : 0 }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700/60 text-center text-xs font-medium text-slate-400 dark:text-slate-500 print:mt-16">
                <p>{{ completedCount }} completed task{{ completedCount === 1 ? '' : 's' }} out of {{ total }} total.</p>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
        background: white !important;
    }
    html {
        background: white !important;
    }
    .print\:bg-white { background: white !important; }
    .print\:border-none { border: none !important; }
    .print\:shadow-none { box-shadow: none !important; }
    .print\:p-0 { padding: 0 !important; }
    .print\:mb-8 { margin-bottom: 2rem !important; }
    .print\:hidden { display: none !important; }
    .print\:border-slate-300 { border-color: #cbd5e1 !important; }
}
</style>
