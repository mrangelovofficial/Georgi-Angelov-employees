<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { calculate } from '@/routes/team-period';

type ResultRow = {
    employee_id_1: number;
    employee_id_2: number;
    project_id: number;
    days_worked: number;
};

defineProps<{
    results?: ResultRow[];
}>();

const file = ref<File | null>(null);
const isDragging = ref(false);
const isUploading = ref(false);

const selectFile = (event: Event) => {
    const input = event.target as HTMLInputElement;

    if (!input.files?.length) {
        return;
    }

    uploadFile(input.files[0]);
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;

    const droppedFile = event.dataTransfer?.files?.[0];

    if (!droppedFile) {
        return;
    }

    uploadFile(droppedFile);
};

const uploadFile = (selectedFile: File) => {
    if (!selectedFile.name.toLowerCase().endsWith('.csv')) {
        return;
    }

    file.value = selectedFile;
    isUploading.value = true;

    router.post(
        calculate.url(),
        {
            file: selectedFile,
        },
        {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => {
                isUploading.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Employee Projects" />

    <main class="min-h-screen bg-gray-50 p-6">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-6">
                <label
                    class="flex min-h-64 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-6 text-center transition"
                    :class="
                        isDragging
                            ? 'border-gray-500 bg-gray-50'
                            : 'border-gray-300 hover:border-gray-400'
                    "
                    @dragenter.prevent="isDragging = true"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                >
                    <svg
                        class="mb-4 h-10 w-10 text-gray-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 16V4m0 0L8 8m4-4 4 4M6 20h12"
                        />
                    </svg>

                    <p class="text-base font-medium text-gray-900">
                        Drag & drop a CSV file here
                    </p>

                    <p class="my-2 text-sm text-gray-400">
                        or
                    </p>

                    <span
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Choose file
                    </span>

                    <input
                        type="file"
                        accept=".csv,text/csv"
                        class="hidden"
                        @change="selectFile"
                    />
                </label>

                <div
                    v-if="file"
                    class="mt-4 flex items-center gap-2 text-sm text-gray-600"
                >
                    <svg
                        v-if="!isUploading"
                        class="h-5 w-5 text-green-500"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.7a1 1 0 00-1.4-1.4L9 10.2 7.7 8.9a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>

                    <span>
                        {{
                            isUploading
                                ? `Processing ${file.name}...`
                                : file.name
                        }}
                    </span>
                </div>
            </section>

            <section
                v-if="results?.length"
                class="overflow-hidden rounded-lg border border-gray-200 bg-white"
            >
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Common Projects
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-6 py-3 font-medium">
                                    Employee ID #1
                                </th>

                                <th class="px-6 py-3 font-medium">
                                    Employee ID #2
                                </th>

                                <th class="px-6 py-3 font-medium">
                                    Project ID
                                </th>

                                <th class="px-6 py-3 font-medium">
                                    Days worked
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="row in results"
                                :key="`${row.employee_id_1}-${row.employee_id_2}-${row.project_id}`"
                            >
                                <td class="px-6 py-3 text-gray-700">
                                    {{ row.employee_id_1 }}
                                </td>

                                <td class="px-6 py-3 text-gray-700">
                                    {{ row.employee_id_2 }}
                                </td>

                                <td class="px-6 py-3 text-gray-700">
                                    {{ row.project_id }}
                                </td>

                                <td class="px-6 py-3 text-gray-700">
                                    {{ row.days_worked }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</template>