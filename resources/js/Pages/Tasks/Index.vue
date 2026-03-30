<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    tasks: Object
});

const isModalOpen = ref(false);
const isStatsModalOpen = ref(false);
const filterStatus = ref('All Status');
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

// Prevent selecting past dates in HTML input
const minDate = new Date().toISOString().split('T')[0];

const taskStats = computed(() => {
    const total = props.tasks.data.length;
    const pending = props.tasks.data.filter(t => t.status === 'pending').length;
    const inProgress = props.tasks.data.filter(t => t.status === 'in_progress').length;
    const completed = props.tasks.data.filter(t => t.status === 'done').length;

    return {
        total,
        pending,
        inProgress,
        completed,
    };
});

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
                popup: 'task-alert-popup border-none shadow-2xl',
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

const deleteTask = (task) => {
    if (task.status !== 'done') {
        Swal.fire({
            title: 'Cannot Delete Task',
            text: `This task is currently "${task.status.replace('_', ' ')}". Only completed tasks can be deleted.`,
            icon: 'error',
            confirmButtonColor: '#64748b',
            confirmButtonText: 'Got it',
            background: document.documentElement.classList.contains('dark') ? '#2d3748' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#1e293b',
            customClass: {
                popup: 'task-alert-popup border-none shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-2.5'
            }
        });
        return;
    }

    Swal.fire({
        title: 'Delete completed task?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Delete Task',
        background: document.documentElement.classList.contains('dark') ? '#2d3748' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#1e293b',
        customClass: {
            popup: 'task-alert-popup border-none shadow-2xl',
            confirmButton: 'rounded-xl px-6 py-2.5',
            cancelButton: 'rounded-xl px-6 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/tasks/${task.id}`);
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
    <Head title="Tasks" />

    <div class="min-h-screen bg-slate-100 dark:bg-slate-900 transition-colors duration-300">
        <!-- Navigation -->
        <nav class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 bg-emerald-600 rounded-md flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-slate-100 truncate">
                        Task Management
                    </span>
                 </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <button @click="toggleTheme" class="rounded-md p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white" title="Toggle Dark Mode">
                        <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    <button @click="isModalOpen = true" class="flex items-center gap-2 rounded-md bg-emerald-600 px-3 sm:px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-500 sm:text-base">
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
                <div
                    v-for="stat in [
                    { label: 'Total Tasks', value: taskStats.total },
                    { label: 'Pending', value: taskStats.pending },
                    { label: 'In Progress', value: taskStats.inProgress },
                    { label: 'Completed', value: taskStats.completed }
                ]"
                    :key="stat.label"
                    class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800 sm:p-5"
                >
                    <p class="mb-1 text-xs font-medium text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
                    <h3 class="text-2xl font-semibold text-slate-900 dark:text-slate-100 sm:text-3xl">{{ stat.value }}</h3>
                </div>
            </div>

            <!-- Task List -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <div class="flex flex-row items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-700 dark:bg-slate-900/40 sm:px-6">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100">Tasks</h2>
                    <div class="flex-shrink-0">
                        <select v-model="filterStatus" class="w-28 rounded-md border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-800 outline-none transition-colors focus:border-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 sm:w-auto sm:px-3 sm:text-sm">
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
                            <tr class="whitespace-nowrap border-b border-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                <th class="px-4 py-4 sm:px-6">Task Details</th>
                                <th class="px-4 py-4 text-center sm:px-6">Priority</th>
                                <th class="px-4 py-4 text-center sm:px-6">Due Date</th>
                                <th class="px-4 py-4 text-center sm:px-6">Status</th>
                                <th class="px-4 py-4 text-right sm:px-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-if="filteredTasks.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-sm italic text-slate-400 dark:text-slate-500">No tasks found for the selected filter.</td>
                            </tr>
                            <tr v-for="task in filteredTasks" :key="task.id" class="whitespace-nowrap transition-colors hover:bg-slate-50 dark:hover:bg-slate-900/40">
                                <td class="px-4 py-4 sm:px-6">
                                    <div class="max-w-[150px] truncate text-sm font-medium leading-tight text-slate-900 dark:text-slate-100 sm:max-w-none sm:whitespace-normal">{{ task.title }}</div>
                                </td>
                                <td class="px-4 py-4 text-center sm:px-6">
                                    <span :class="['rounded-md px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide', getPriorityColor(task.priority)]">
                                        {{ task.priority }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center sm:px-6">
                                    <span class="text-xs text-slate-600 dark:text-slate-400">{{ task.due_date }}</span>
                                </td>
                                <td class="px-4 py-4 text-center sm:px-6">
                                    <span :class="['rounded-md px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide', getStatusColor(task.status)]">
                                        {{ task.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right sm:px-6">
                                    <div class="flex items-center justify-end gap-1">
                                        <button v-if="task.status !== 'done'" @click="updateStatus(task)" class="rounded-md p-1.5 text-emerald-600 transition-colors hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/30 sm:p-2" title="Next Status">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <button @click="deleteTask(task)" class="rounded-md p-1.5 text-red-500 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 sm:p-2" title="Delete Task">
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
            <div class="mt-8 rounded-xl border border-slate-800 bg-slate-900 p-6 text-white dark:border-slate-700 dark:bg-slate-900 sm:p-6">
                <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h2 class="mb-2 text-lg font-semibold text-white sm:text-xl">Task Report</h2>
                        <p class="max-w-md text-sm text-slate-300">{{ taskStats.completed }} of {{ taskStats.total }} tasks are completed. Open the report or summary for the current status breakdown.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 w-full sm:w-auto">
                        <Link href="/tasks/report" class="rounded-md bg-white px-4 py-2 text-center text-sm font-medium text-slate-900 no-underline transition-colors hover:bg-slate-100">
                            View Report
                        </Link>
                        <button @click="isStatsModalOpen = true" class="rounded-md border border-white/10 bg-slate-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-slate-600">
                            Status Summary
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- New Task Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-900/40">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">Create New Task</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="space-y-4 p-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Task Title</label>
                        <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition-colors placeholder-slate-400 focus:border-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-400" placeholder="What needs to be done?" required>
                        <div v-if="form.errors.title" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ form.errors.title }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Due Date</label>
                            <input v-model="form.due_date" type="date" :min="minDate" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition-colors focus:border-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" required>
                            <div v-if="form.errors.due_date" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ form.errors.due_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                            <select v-model="form.priority" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition-colors focus:border-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            <div v-if="form.errors.priority" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ form.errors.priority }}</div>
                        </div>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="isModalOpen = false" class="flex-1 rounded-md border border-slate-300 px-4 py-2.5 font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700/50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing" class="flex-1 rounded-md bg-emerald-600 px-4 py-2.5 font-medium text-white transition-colors hover:bg-emerald-500 disabled:opacity-50">
                            {{ form.processing ? 'Creating...' : 'Create Task' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Modal -->
        <div v-if="isStatsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-900/40">
                    <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Task Summary</h3>
                    <button @click="isStatsModalOpen = false" class="text-slate-400 transition-colors hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-6 p-5">
                    <p class="text-sm text-slate-600 dark:text-slate-300">Current task counts grouped by status.</p>
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Status Distribution</h4>
                        <div v-for="status in ['pending', 'in_progress', 'done']" :key="status" class="space-y-1">
                            <div class="flex justify-between text-xs font-medium">
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
                        <button @click="isStatsModalOpen = false" class="w-full rounded-md bg-slate-800 py-3 font-medium text-white transition-colors hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.task-alert-popup.swal2-popup {
    border-radius: 16px !important;
}

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
