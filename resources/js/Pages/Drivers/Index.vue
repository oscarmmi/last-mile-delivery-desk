<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    drivers: { type: Array, required: true },
});

const page = usePage();
const editingId = ref(null);
const form = useForm({ name: '', phone: '', vehicle: 'Motorcycle', is_active: true });
const editing = computed(() => editingId.value !== null);

function resetForm() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    form.vehicle = 'Motorcycle';
}

function edit(driver) {
    editingId.value = driver.id;
    form.name = driver.name;
    form.phone = driver.phone ?? '';
    form.vehicle = driver.vehicle;
    form.is_active = driver.is_active;
    form.clearErrors();
}

function save() {
    if (editing.value) {
        form.put(route('drivers.update', editingId.value), { onSuccess: resetForm });
        return;
    }

    form.post(route('drivers.store'), { onSuccess: resetForm });
}

function remove(driver) {
    if (window.confirm(`Remove ${driver.name} from the team?`)) {
        form.delete(route('drivers.destroy', driver.id));
    }
}
</script>

<template>
    <Head title="Drivers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">People & fleet</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Drivers</h1>
                </div>
                <p class="text-sm text-slate-500">{{ drivers.length }} team members</p>
            </div>
        </template>

        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:px-8">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h2 class="font-semibold text-slate-900">Your delivery team</h2>
                    <p class="mt-1 text-sm text-slate-500">Keep driver contact and vehicle details up to date.</p>
                </div>
                <div v-if="drivers.length" class="divide-y divide-slate-100">
                    <article v-for="driver in drivers" :key="driver.id" class="flex flex-wrap items-center justify-between gap-4 px-6 py-5">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-sm font-bold text-emerald-800">
                                {{ driver.name.split(' ').map((part) => part[0]).slice(0, 2).join('') }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-slate-900">{{ driver.name }}</h3>
                                    <span :class="driver.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="rounded-full px-2 py-0.5 text-[11px] font-semibold">
                                        {{ driver.is_active ? 'Active' : 'Off duty' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ driver.vehicle }} <span v-if="driver.phone">· {{ driver.phone }}</span></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button class="text-sm font-semibold text-emerald-800 hover:text-emerald-950" @click="edit(driver)">Edit</button>
                            <button class="text-sm font-medium text-slate-400 hover:text-rose-700" @click="remove(driver)">Remove</button>
                        </div>
                    </article>
                </div>
                <div v-else class="px-6 py-14 text-center">
                    <p class="font-medium text-slate-800">No drivers yet</p>
                    <p class="mt-1 text-sm text-slate-500">Add your first driver to build your delivery team.</p>
                </div>
            </section>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Team setup</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">{{ editing ? 'Update driver' : 'Add a driver' }}</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Driver details help dispatch keep each route accountable.</p>

                <form class="mt-6 space-y-4" @submit.prevent="save">
                    <div>
                        <InputLabel for="name" value="Full name" />
                        <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autocomplete="name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="phone" value="Phone (optional)" />
                        <TextInput id="phone" v-model="form.phone" class="mt-1 block w-full" autocomplete="tel" />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>
                    <div>
                        <InputLabel for="vehicle" value="Vehicle" />
                        <select id="vehicle" v-model="form.vehicle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            <option>Motorcycle</option><option>Bicycle</option><option>Van</option><option>Car</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.vehicle" />
                    </div>
                    <label class="flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-700" />
                        Available for dispatch
                    </label>
                    <InputError :message="form.errors.driver || page.props.errors?.driver" />
                    <div class="flex items-center gap-3 pt-1">
                        <PrimaryButton :disabled="form.processing">{{ editing ? 'Save changes' : 'Add driver' }}</PrimaryButton>
                        <button v-if="editing" type="button" class="text-sm font-medium text-slate-500 hover:text-slate-800" @click="resetForm">Cancel</button>
                    </div>
                    <p v-if="page.props.flash?.success" class="text-sm font-medium text-emerald-700" role="status">{{ page.props.flash.success }}</p>
                </form>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>
