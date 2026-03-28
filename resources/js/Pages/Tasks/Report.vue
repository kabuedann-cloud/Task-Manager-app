<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    report: Object,
    total: Number
});

const getStatusColor = (status) => {
    const colors = {
        pending: 'text-slate-600',
        in_progress: 'text-blue-600',
        done: 'text-green-600',
    };
    return colors[status?.toLowerCase()] || 'text-gray-600';
};

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head title="Daily Task Report" />

    <div class="min-h-screen bg-white p-6 sm:p-12 md:p-16 max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 mb-12 border-b pb-8">
            <div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 mb-2">Daily Task Report</h1>
                <p class="text-slate-500 font-medium text-sm sm:text-base">Generated on {{ new Date().toLocaleDateString() }}</p>
            </div>
            <div class="flex flex-wrap gap-3 sm:gap-4 no-print w-full sm:w-auto">
                <button @click="printPage" class="flex-1 sm:flex-none px-4 sm:px-5 py-2 bg-slate-900 text-white rounded-lg font-bold hover:bg-slate-800 transition-all flex items-center justify-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print PDF
                </button>
                <Link href="/" class="flex-1 sm:flex-none px-4 sm:px-5 py-2 border border-slate-200 text-slate-600 rounded-lg font-bold hover:bg-slate-50 transition-all text-center text-sm no-underline whitespace-nowrap">
                    Dashboard
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-8 mb-12">
            <div class="bg-slate-50 p-6 sm:p-8 rounded-3xl border border-slate-100 text-center sm:text-left">
                <p class="text-[10px] sm:text-sm font-bold text-slate-400 uppercase tracking-widest mb-2">Total Tasks Managed</p>
                <h2 class="text-4xl sm:text-5xl font-black text-slate-900">{{ total }}</h2>
            </div>
            <div class="bg-emerald-50 p-6 sm:p-8 rounded-3xl border border-emerald-100 text-center sm:text-left">
                <p class="text-[10px] sm:text-sm font-bold text-emerald-400 uppercase tracking-widest mb-2">Completion Rate</p>
                <h2 class="text-4xl sm:text-5xl font-black text-emerald-900">
                    {{ total ? Math.round((report.data.find(r => r.status === 'done')?.count || 0) / total * 100) : 0 }}%
                </h2>
            </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-3xl">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] sm:text-xs font-black tracking-widest">
                        <th class="px-6 sm:px-8 py-5">Task Status</th>
                        <th class="px-6 sm:px-8 py-5 text-right">Count</th>
                        <th class="px-6 sm:px-8 py-5 text-right">Percentage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="item in report.data" :key="item.status" class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-6 font-bold text-slate-900 capitalize">
                            <span :class="getStatusColor(item.status)">●</span>
                            {{ item.status.replace('_', ' ') }}
                        </td>
                        <td class="px-8 py-6 text-right font-black text-slate-900">{{ item.count }}</td>
                        <td class="px-8 py-6 text-right font-medium text-slate-500">
                            {{ total ? Math.round(item.count / total * 100) : 0 }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-16 pt-8 border-t border-dashed border-slate-200 text-center">
            <p class="text-slate-400 text-sm font-medium italic">This report was generated automatically.</p>
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
    }
}
</style>
