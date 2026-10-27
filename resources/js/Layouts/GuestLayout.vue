<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-slate-50 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-14 w-14 text-emerald-800" />
            </Link>
        </div>

        <div
            class="mt-5 w-full overflow-hidden rounded-xl border border-slate-200 bg-white px-6 py-5 shadow-sm sm:max-w-md"
        >
            <slot />
        </div>
    </div>
</template>
