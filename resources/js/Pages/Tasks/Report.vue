<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

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

const completionRate = computed(() => {
    if (!props.total) return 0;
    const doneCount = props.report.data.find(r => r.status === 'done')?.count || 0;
    return Math.round((doneCount / props.total) * 100);
});
</script>

<template>
    <Head title="Activity Report" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 transition-colors duration-300 py-8 px-4 sm:px-6 lg:px-8 print:bg-white print:py-0">
        <div class="max-w-5xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-5 mb-8 bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 print:border-none print:shadow-none print:p-0 print:mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20 text-white print:hidden flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-1 flex items-center gap-3">
                            Activity Report
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Generated on {{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 w-full md:w-auto mt-2 md:mt-0 no-print">
                    <Link href="/" class="flex-1 md:flex-none px-5 py-2 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg font-bold hover:bg-slate-50 dark:hover:bg-slate-600 transition-all text-center text-sm no-underline flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Dashboard
                    </Link>
                     <button @click="printPage" class="flex-1 md:flex-none px-5 py-2 border border-slate-800 bg-slate-900 dark:bg-emerald-600 dark:border-emerald-600 text-white rounded-lg font-bold hover:bg-slate-800 dark:hover:bg-emerald-500 transition-all shadow-md flex items-center justify-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Report
                    </button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-slate-400/5 rounded-full blur-2xl transition-colors"></div>
                    <div class="flex items-center gap-3 mb-2 relative z-10 w-full">
                        <div class="w-8 h-8 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-lg flex items-center justify-center">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Total Monitored</h3>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white relative z-10 pl-1">{{ total }}</h2>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors"></div>
                    <div class="flex items-center gap-3 mb-2 relative z-10">
                        <div class="w-8 h-8 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Success Rate</h3>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white relative z-10 flex items-baseline gap-1 pl-1">
                        {{ completionRate }}<span class="text-xl font-bold text-slate-400">%</span>
                    </h2>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden print:border-slate-300">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-3">
                        Status Distribution Details
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700/60 text-slate-400 dark:text-slate-500 uppercase text-[10px] sm:text-xs font-bold tracking-widest">
                                <th class="px-6 py-4">Pipeline Stage</th>
                                <th class="px-6 py-4 text-center">Volume</th>
                                <th class="px-6 py-4 text-right w-1/3">Distribution</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-700/30">
                            <tr v-if="report.data.length === 0">
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500 italic text-sm">No task data available for the generated timeframe.</td>
                            </tr>
                            <tr v-for="item in report.data" :key="item.status" class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div :class="['w-2 h-2 rounded-full shadow-[0_0_8px] ring-2 ring-white dark:ring-slate-800', getStatusDotColor(item.status), getStatusDotColor(item.status).replace('bg-', 'shadow-').replace('500','500/50')]"></div>
                                        <span class="font-bold text-sm text-slate-700 dark:text-slate-200 capitalize tracking-wide">{{ item.status.replace('_', ' ') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span :class="['px-3 py-1 rounded-full text-xs font-bold inline-block', getStatusColor(item.status)]">
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
            <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700/60 text-center flex flex-col sm:flex-row justify-between items-center gap-3 text-xs font-medium text-slate-400 dark:text-slate-500 print:mt-16">
                <p>Automated System Report &bull; Task Manager App</p>
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    System is operational
                </div>
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
