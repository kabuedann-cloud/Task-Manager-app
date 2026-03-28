<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tasks: Object
});

const isModalOpen = ref(false);
const isStatsModalOpen = ref(false);
const filterStatus = ref('All Status');

const filteredTasks = computed(() => {
    if (filterStatus.value === 'All Status') return props.tasks.data;
    return props.tasks.data.filter(t => t.status.toLowerCase().replace(' ', '_') === filterStatus.value.toLowerCase().replace(' ', '_'));
});

const form = useForm({
    title: '',
    due_date: '',
    priority: 'medium',
});

const submit = () => {
    form.post('/tasks', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const updateStatus = (task) => {
    let nextStatus = '';
    let statusLabel = '';
    if (task.status === 'pending') {
        nextStatus = 'in_progress';
        statusLabel = 'In Progress';
    } else if (task.status === 'in_progress') {
        nextStatus = 'done';
        statusLabel = 'Completed';
    }
    
    if (nextStatus) {
        Swal.fire({
            title: 'Update Task Status?',
            text: `Move this task to ${statusLabel}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, update it!',
            background: document.documentElement.classList.contains('dark') ? '#2d3748' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#1e293b',
            customClass: {
                popup: 'rounded-[32px] border-none shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-2.5',
                cancelButton: 'rounded-xl px-6 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                router.patch(`/tasks/${task.id}/status`, {
                    status: nextStatus
                });
            }
        });
    }
};

const deleteTask = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!',
        background: document.documentElement.classList.contains('dark') ? '#2d3748' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#1e293b',
        customClass: {
            popup: 'rounded-[32px] border-none shadow-2xl',
            confirmButton: 'rounded-xl px-6 py-2.5',
            cancelButton: 'rounded-xl px-6 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/tasks/${id}`);
        }
    });
};

const getPriorityColor = (priority) => {
    const colors = {
        high: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        low: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    };
    return colors[priority] || 'bg-gray-100 text-gray-700';
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-300',
        in_progress: 'bg-blue-950/10 text-blue-900 dark:bg-blue-950/60 dark:text-blue-200',
        done: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="Task Dashboard" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-800 transition-colors duration-300">
        <!-- Navigation -->
        <nav class="sticky top-0 z-40 w-full backdrop-blur-md bg-white/80 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700">
            <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-600 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="text-lg sm:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-600 dark:from-slate-100 dark:to-slate-400 truncate">
                        Task Management
                    </span>
                 </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <button @click="isModalOpen = true" class="px-3 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-medium transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 text-sm sm:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">New Task</span>
                        <span class="sm:hidden">Add</span>
                    </button>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Summary Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                <div v-for="stat in [
                    { label: 'Total Tasks', value: tasks.data.length },
                    { label: 'Pending', value: tasks.data.filter(t => t.status === 'pending').length },
                    { label: 'In Progress', value: tasks.data.filter(t => t.status === 'in_progress').length },
                    { label: 'Completed', value: tasks.data.filter(t => t.status === 'done').length }
                ]" :key="stat.label" class="bg-white dark:bg-slate-700 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-600 shadow-sm hover:shadow-md transition-shadow">
                    <p class="text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">{{ stat.label }}</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ stat.value }}</h3>
                </div>
            </div>

            <!-- Task List -->
            <div class="bg-white dark:bg-slate-700 rounded-3xl border border-slate-200 dark:border-slate-600 shadow-xl overflow-hidden">
                <div class="px-4 sm:px-8 py-6 border-b border-slate-200 dark:border-slate-600 flex flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        Recent Tasks
                        <span class="hidden sm:inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 text-xs rounded-full">Development</span>
                    </h2>
                    <div class="flex-shrink-0">
                        <select v-model="filterStatus" class="bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 text-xs sm:text-sm text-slate-800 dark:text-slate-200 rounded-lg px-2 sm:px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 outline-none transition-all w-28 sm:w-auto">
                            <option>All Status</option>
                            <option>Pending</option>
                            <option>In Progress</option>
                            <option>Done</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-400 border-b border-slate-100 dark:border-slate-600 whitespace-nowrap">
                                <th class="px-4 sm:px-8 py-5">Task Details</th>
                                <th class="px-4 sm:px-8 py-5 text-center">Priority</th>
                                <th class="px-4 sm:px-8 py-5 text-center">Due Date</th>
                                <th class="px-4 sm:px-8 py-5 text-center">Status</th>
                                <th class="px-4 sm:px-8 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-600/50">
                            <tr v-if="filteredTasks.length === 0">
                                <td colspan="5" class="px-8 py-12 text-center text-slate-400 dark:text-slate-500 italic">No tasks found. Create one to get started!</td>
                            </tr>
                            <tr v-for="task in filteredTasks" :key="task.id" class="group hover:bg-slate-50/50 dark:hover:bg-slate-600/40 transition-colors whitespace-nowrap">
                                <td class="px-4 sm:px-8 py-4 sm:py-6">
                                    <div class="font-bold text-slate-900 dark:text-slate-100 text-sm leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors max-w-[150px] sm:max-w-none truncate sm:whitespace-normal">{{ task.title }}</div>
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6 text-center">
                                    <span :class="['text-[10px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider', getPriorityColor(task.priority)]">
                                        {{ task.priority }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6 text-center">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ task.due_date }}</span>
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6 text-center">
                                    <span :class="['px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide', getStatusColor(task.status)]">
                                        {{ task.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6 text-right">
                                    <div class="flex items-center justify-end gap-0.5 sm:gap-1">
                                        <button v-if="task.status !== 'done'" @click="updateStatus(task)" class="p-1.5 sm:p-2 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-all" title="Next Status">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <button v-if="task.status === 'done'" @click="deleteTask(task.id)" class="p-1.5 sm:p-2 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-all" title="Delete Task">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Daily Report Banner -->
            <div class="mt-8 bg-slate-900 dark:bg-slate-900/80 rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-2xl border border-slate-700/50">
                <div class="relative z-10 flex flex-col items-center justify-between gap-6 sm:gap-8 text-center md:text-left md:flex-row">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold mb-2">Daily Performance Report</h2>
                        <p class="text-slate-300 text-sm max-w-sm">You have completed <span class="text-emerald-400 font-bold">{{ tasks.data.length ? Math.round((tasks.data.filter(t => t.status === 'done').length / tasks.data.length) * 100) : 0 }}%</span> of your tasks for today. Keep up the momentum!</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 w-full sm:w-auto">
                        <Link href="/tasks/report" class="px-6 py-3 bg-white text-slate-900 font-bold rounded-xl hover:bg-slate-100 transition-colors shadow-lg shadow-black/20 no-underline text-center text-sm">
                            View Report
                        </Link>
                        <button @click="isStatsModalOpen = true" class="px-6 py-3 bg-slate-700 text-white font-bold rounded-xl hover:bg-slate-600 transition-colors border border-white/10 text-sm">
                            View Stats
                        </button>
                    </div>
                </div>
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-10 -top-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>
            </div>
        </main>

        <!-- New Task Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-[2px]">
            <div class="bg-white dark:bg-slate-700 w-full max-w-md rounded-[32px] shadow-2xl border border-slate-200 dark:border-slate-600 overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-600 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/60">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">Create New Task</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Task Title</label>
                        <input v-model="form.title" type="text" class="w-full bg-slate-50 dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-400" placeholder="What needs to be done?" required>
                        <div v-if="form.errors.title" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ form.errors.title }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Due Date</label>
                            <input v-model="form.due_date" type="date" class="w-full bg-slate-50 dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-slate-900 dark:text-slate-100" required>
                            <div v-if="form.errors.due_date" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ form.errors.due_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                            <select v-model="form.priority" class="w-full bg-slate-50 dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-slate-900 dark:text-slate-100">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            <div v-if="form.errors.priority" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ form.errors.priority }}</div>
                        </div>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="isModalOpen = false" class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-500 text-slate-600 dark:text-slate-300 font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-500 transition-all shadow-lg shadow-emerald-600/20 disabled:opacity-50">
                            {{ form.processing ? 'Creating...' : 'Create Task' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Modal -->
        <div v-if="isStatsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-[2px]">
            <div class="bg-white dark:bg-slate-700 w-full max-w-lg rounded-[32px] shadow-2xl border border-slate-200 dark:border-slate-600 overflow-hidden">
                <div class="p-8 border-b border-slate-100 dark:border-slate-600 flex justify-between items-center bg-emerald-600">
                    <h3 class="text-2xl font-black text-white">Task Overview</h3>
                    <button @click="isStatsModalOpen = false" class="text-white/80 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-6 bg-slate-50 dark:bg-slate-600 rounded-2xl border border-slate-100 dark:border-slate-500">
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Success Rate</p>
                            <p class="text-3xl font-black text-slate-900 dark:text-slate-100">99.9%</p>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-600 rounded-2xl border border-slate-100 dark:border-slate-500">
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-widest mb-1">Latency</p>
                            <p class="text-3xl font-black text-slate-900 dark:text-slate-100">42ms</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest">Status Distribution</h4>
                        <div v-for="status in ['pending', 'in_progress', 'done']" :key="status" class="space-y-1">
                            <div class="flex justify-between text-xs font-bold uppercase">
                                <span class="text-slate-500 dark:text-slate-400">{{ status.replace('_', ' ') }}</span>
                                <span class="text-slate-900 dark:text-slate-200">{{ tasks.data.filter(t => t.status === status).length }} tasks</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-500 h-2 rounded-full overflow-hidden">
                                <div :class="['h-full transition-all duration-1000', 
                                    status === 'done' ? 'bg-emerald-500' : 
                                    status === 'in_progress' ? 'bg-blue-900' : 'bg-slate-400']"
                                    :style="{ width: `${tasks.data.length ? (tasks.data.filter(t => t.status === status).length / tasks.data.length * 100) : 0}%` }">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4">
                        <button @click="isStatsModalOpen = false" class="w-full py-4 bg-slate-800 dark:bg-slate-900 text-white font-black rounded-xl hover:opacity-90 transition-all shadow-xl">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark ::-webkit-scrollbar-thumb {
    background: #475569;
}
</style>
