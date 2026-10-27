<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    drivers: { type: Array, required: true },
    unassignedStops: { type: Array, required: true },
    statusCounts: { type: Object, required: true },
    totalDrivers: { type: Number, required: true },
    activeDriverCount: { type: Number, required: true },
});

const page = usePage();
const showStopForm = ref(false);
const stopForm = useForm({ address: '', window_starts_at: '', window_ends_at: '' });
const statusRows = [
    { key: 'queued', label: 'Queued', dot: 'bg-amber-500' },
    { key: 'out_for_delivery', label: 'On route', dot: 'bg-sky-600' },
    { key: 'delivered', label: 'Delivered', dot: 'bg-emerald-600' },
    { key: 'failed', label: 'Failed', dot: 'bg-rose-600' },
];

const boardColumns = computed(() => [
    { id: 'unassigned', name: 'Unassigned', vehicle: 'Needs a driver', is_active: true, counts: null, stops: props.unassignedStops },
    ...props.drivers,
]);

const totalStops = computed(() => Object.values(props.statusCounts).reduce((total, count) => total + count, 0));

function driversForStop(stop) {
    return props.drivers.filter((driver) => driver.is_active || driver.id === stop.driver_id);
}

function statusLabel(status) {
    return statusRows.find((row) => row.key === status)?.label ?? status;
}

function assign(stop, event) {
    const selectedDriver = event.target.value;
    router.patch(route('stops.assign', stop.id), {
        driver_id: selectedDriver === '' ? null : Number(selectedDriver),
    }, { preserveScroll: true });
}

function advance(stop, status) {
    router.patch(route('stops.status', stop.id), { status }, { preserveScroll: true });
}

function addStop() {
    stopForm.post(route('stops.store'), {
        preserveScroll: true,
        onSuccess: () => {
            stopForm.reset();
            showStopForm.value = false;
        },
    });
}

function initials(name) {
    return name.split(' ').map((part) => part[0]).slice(0, 2).join('');
}
</script>

<template>
    <Head title="Dispatch board" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-end justify-between gap-5">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-950">Dispatch board</h1>
                    <p class="mt-1 text-sm text-slate-500">A clear view of today’s delivery work, driver by driver.</p>
                </div>
                <button
                    class="inline-flex min-h-11 items-center gap-2 rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2"
                    @click="showStopForm = !showStopForm"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                    Add stop
                </button>
            </div>
        </template>

        <main class="min-h-[calc(100vh-8rem)] bg-slate-50">
            <div class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
                <section class="flex flex-wrap items-center justify-between gap-5 border-b border-slate-200 pb-5" aria-label="Dispatch summary">
                    <div class="flex flex-wrap items-center gap-x-7 gap-y-3">
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-semibold tabular-nums text-slate-950">{{ totalStops }}</span>
                            <span class="text-sm text-slate-500">stops in the system</span>
                        </div>
                        <div class="h-7 w-px bg-slate-200" aria-hidden="true" />
                        <div class="flex flex-wrap gap-x-5 gap-y-2">
                            <div v-for="row in statusRows" :key="row.key" class="flex items-center gap-2 text-sm">
                                <span :class="row.dot" class="h-2 w-2 rounded-full" aria-hidden="true" />
                                <span class="text-slate-500">{{ row.label }}</span>
                                <span class="font-semibold tabular-nums text-slate-900">{{ statusCounts[row.key] }}</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500">
                        <span class="font-semibold text-slate-900">{{ activeDriverCount }}</span> of {{ totalDrivers }} drivers available
                    </p>
                </section>

                <section v-if="showStopForm" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50/70 p-5 sm:p-6" aria-labelledby="new-stop-heading">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 id="new-stop-heading" class="text-lg font-semibold text-slate-950">Add a delivery stop</h2>
                            <p class="mt-1 text-sm text-slate-600">New stops start in the unassigned queue.</p>
                        </div>
                        <button class="rounded-md p-1 text-slate-500 hover:bg-white hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-700" aria-label="Close stop form" @click="showStopForm = false">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" /></svg>
                        </button>
                    </div>
                    <form class="mt-5 grid gap-4 md:grid-cols-[minmax(220px,1.5fr)_1fr_1fr_auto] md:items-end" @submit.prevent="addStop">
                        <div>
                            <InputLabel for="stop-address" value="Delivery address" />
                            <TextInput id="stop-address" v-model="stopForm.address" class="mt-1 block w-full" placeholder="Street, building, neighborhood" required maxlength="255" />
                            <InputError class="mt-1" :message="stopForm.errors.address" />
                        </div>
                        <div>
                            <InputLabel for="window-start" value="Window starts" />
                            <TextInput id="window-start" v-model="stopForm.window_starts_at" type="datetime-local" class="mt-1 block w-full" required />
                            <InputError class="mt-1" :message="stopForm.errors.window_starts_at" />
                        </div>
                        <div>
                            <InputLabel for="window-end" value="Window ends" />
                            <TextInput id="window-end" v-model="stopForm.window_ends_at" type="datetime-local" class="mt-1 block w-full" required />
                            <InputError class="mt-1" :message="stopForm.errors.window_ends_at" />
                        </div>
                        <PrimaryButton :disabled="stopForm.processing" class="min-h-10 justify-center">
                            {{ stopForm.processing ? 'Adding…' : 'Add to queue' }}
                        </PrimaryButton>
                    </form>
                    <p v-if="stopForm.errors.window_ends_at" class="mt-3 text-sm text-rose-700" role="alert">Choose an end time at or after the start time.</p>
                </section>

                <div v-if="page.props.flash?.success" class="mt-5 rounded-lg border border-emerald-200 bg-white px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                    {{ page.props.flash.success }}
                </div>

                <section class="mt-6" aria-label="Stops grouped by driver">
                    <div class="mb-3 flex items-center justify-between gap-4">
                        <h2 class="text-sm font-semibold text-slate-800">Driver lanes</h2>
                        <p class="text-xs text-slate-500">Assign stops and advance their delivery status</p>
                    </div>
                    <div class="-mx-4 overflow-x-auto px-4 pb-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                        <div class="flex min-h-[460px] items-start gap-4">
                            <section
                                v-for="column in boardColumns"
                                :key="column.id"
                                class="w-[300px] shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100/70"
                                :aria-label="`${column.name} lane`"
                            >
                                <header class="border-b border-slate-200 bg-white px-4 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div v-if="column.id === 'unassigned'" class="grid h-9 w-9 place-items-center rounded-lg bg-amber-100 text-amber-800">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 3.5v6m0 3.5h.01M3.9 16.5h12.2c.8 0 1.3-.9.9-1.6L11 4.3a1.2 1.2 0 0 0-2 0l-6 10.6c-.4.7.1 1.6.9 1.6Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                            </div>
                                            <div v-else class="relative grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-900">
                                                {{ initials(column.name) }}
                                                <span :class="column.is_active ? 'bg-emerald-500' : 'bg-slate-400'" class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white" />
                                            </div>
                                            <div class="min-w-0">
                                                <h3 class="truncate text-sm font-semibold text-slate-950">{{ column.name }}</h3>
                                                <p class="truncate text-xs text-slate-500">{{ column.vehicle }}</p>
                                            </div>
                                        </div>
                                        <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold tabular-nums text-slate-700">{{ column.stops.length }}</span>
                                    </div>
                                    <div v-if="column.counts" class="mt-3 flex gap-3 border-t border-slate-100 pt-3 text-[11px] text-slate-500">
                                        <span><strong class="font-semibold text-amber-700">{{ column.counts.queued }}</strong> queued</span>
                                        <span><strong class="font-semibold text-sky-700">{{ column.counts.out_for_delivery }}</strong> route</span>
                                        <span><strong class="font-semibold text-emerald-700">{{ column.counts.delivered }}</strong> done</span>
                                        <span><strong class="font-semibold text-rose-700">{{ column.counts.failed }}</strong> failed</span>
                                    </div>
                                </header>

                                <div v-if="column.stops.length" class="space-y-3 p-3">
                                    <article v-for="stop in column.stops" :key="stop.id" class="rounded-lg border border-slate-200 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.05)]">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Stop {{ String(stop.id).padStart(3, '0') }}</p>
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-[11px] font-semibold" :class="{
                                                'bg-amber-50 text-amber-800': stop.status === 'queued',
                                                'bg-sky-50 text-sky-800': stop.status === 'out_for_delivery',
                                                'bg-emerald-50 text-emerald-800': stop.status === 'delivered',
                                                'bg-rose-50 text-rose-800': stop.status === 'failed',
                                            }">
                                                {{ statusLabel(stop.status) }}
                                            </span>
                                        </div>
                                        <p class="mt-3 min-h-10 text-sm font-medium leading-5 text-slate-900">{{ stop.address }}</p>
                                        <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="5.7" stroke="currentColor" stroke-width="1.3"/><path d="M8 4.8V8l2.2 1.3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                                            {{ stop.window }}
                                        </p>

                                        <label class="mt-4 block">
                                            <span class="sr-only">Assign stop {{ stop.id }} to a driver</span>
                                            <select :value="stop.driver_id ?? ''" class="block min-h-9 w-full rounded-md border-slate-200 bg-slate-50 py-1.5 pl-2.5 pr-8 text-xs font-medium text-slate-700 focus:border-emerald-700 focus:ring-emerald-700" @change="assign(stop, $event)">
                                                <option value="">Unassigned</option>
                                                <option v-for="driver in driversForStop(stop)" :key="driver.id" :value="driver.id">{{ driver.name }}{{ driver.is_active ? '' : ' · off duty' }}</option>
                                            </select>
                                        </label>

                                        <div v-if="stop.status === 'queued'" class="mt-3 border-t border-slate-100 pt-3">
                                            <button class="w-full rounded-md bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2" @click="advance(stop, 'out_for_delivery')">Start delivery</button>
                                        </div>
                                        <div v-else-if="stop.status === 'out_for_delivery'" class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
                                            <button class="rounded-md bg-emerald-700 px-2 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2" @click="advance(stop, 'delivered')">Delivered</button>
                                            <button class="rounded-md border border-rose-200 bg-white px-2 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2" @click="advance(stop, 'failed')">Mark failed</button>
                                        </div>
                                        <p v-else class="mt-3 border-t border-slate-100 pt-3 text-xs font-medium text-slate-400">Delivery closed</p>
                                    </article>
                                </div>
                                <div v-else class="m-3 rounded-lg border border-dashed border-slate-300 px-4 py-8 text-center">
                                    <p class="text-sm font-medium text-slate-600">
                                        {{ column.id === 'unassigned' ? (totalStops === 0 ? 'No stops yet' : 'All stops have a driver') : 'No stops assigned' }}
                                    </p>
                                    <p class="mt-1 text-xs leading-5 text-slate-400">
                                        {{ column.id === 'unassigned' ? (totalStops === 0 ? 'Add a stop to start dispatching.' : 'New stops will appear here.') : 'Assign a stop from another lane.' }}
                                    </p>
                                    <button v-if="column.id === 'unassigned' && totalStops === 0" class="mt-3 text-xs font-semibold text-emerald-800 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-950" @click="showStopForm = true">Add your first stop</button>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
