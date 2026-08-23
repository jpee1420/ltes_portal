<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Spinner } from '@/components/ui/spinner';
import {
    ArrowLeft,
    Upload,
    FileSpreadsheet,
    Download,
    CheckCircle2,
    AlertTriangle,
    XCircle,
    FileText,
    Users,
} from '@lucide/vue';

const props = defineProps<{
    sections: Array<{
        id: number;
        name: string;
    }>;
    results?: {
        created: Array<{ row: number; lrn: string; name: string }>;
        skipped: Array<{ row: number; lrn: string; name: string; reason: string }>;
        failed: Array<{ row: number; lrn: string; name: string; errors: string[] }>;
    } | null;
}>();

const form = useForm<{
    file: File | null;
    section_id: string;
}>({
    file: null,
    section_id: '',
});

const fileName = ref<string>('');
const isDragging = ref(false);
const activeTab = ref<'created' | 'skipped' | 'failed'>('created');

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.file = target.files[0];
        fileName.value = target.files[0].name;
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    if (e.dataTransfer?.files && e.dataTransfer.files[0]) {
        const droppedFile = e.dataTransfer.files[0];
        if (droppedFile.name.endsWith('.csv') || droppedFile.type === 'text/csv') {
            form.file = droppedFile;
            fileName.value = droppedFile.name;
        } else {
            alert('Please drop a valid .csv file.');
        }
    }
};

const submit = () => {
    if (!form.file) return;
    form.post('/admin/users/students/import', {
        preserveScroll: true,
        onSuccess: () => {
            if (props.results) {
                if (props.results.created.length > 0) activeTab.value = 'created';
                else if (props.results.skipped.length > 0) activeTab.value = 'skipped';
                else if (props.results.failed.length > 0) activeTab.value = 'failed';
            }
        },
    });
};
</script>

<template>
    <div>
        <Head title="Bulk Student CSV Import — Admin" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/users"
                        class="inline-flex items-center justify-center rounded-lg border border-border bg-background p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        aria-label="Back to Users"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2.5">
                            <Upload class="h-6 w-6 text-primary" />
                            Bulk Student Enrollment Import
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Upload a standard CSV file to register multiple learners for the active academic cycle.
                        </p>
                    </div>
                </div>

                <a
                    href="/admin/users/students/import/template"
                    class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-3.5 py-2 text-xs font-semibold text-foreground shadow-sm transition-colors hover:bg-muted hover:text-primary"
                >
                    <Download class="h-4 w-4 text-primary" />
                    Download CSV Template
                </a>
            </div>

            <!-- Upload Card -->
            <Card class="rounded-xl border border-border/70 shadow-sm">
                <CardHeader class="pb-4">
                    <CardTitle class="text-base font-semibold flex items-center gap-2">
                        <FileSpreadsheet class="h-5 w-5 text-primary" />
                        Select File & Target Homeroom
                    </CardTitle>
                    <CardDescription>
                        Expected CSV columns: <span class="font-mono text-xs text-foreground bg-muted px-1.5 py-0.5 rounded">lrn, first_name, last_name, middle_name, gender, birth_date</span>.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Drag & Drop Zone -->
                        <div
                            class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                            :class="[
                                isDragging
                                    ? 'border-primary bg-primary/5'
                                    : form.file
                                    ? 'border-emerald-500/50 bg-emerald-500/5'
                                    : 'border-border hover:border-primary/50 hover:bg-muted/30',
                            ]"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop"
                        >
                            <input
                                id="csv_file"
                                type="file"
                                accept=".csv,text/csv"
                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                @change="handleFileChange"
                            />

                            <div v-if="form.file" class="flex flex-col items-center gap-2">
                                <div class="h-12 w-12 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                                    <CheckCircle2 class="h-6 w-6" />
                                </div>
                                <div class="font-medium text-sm text-foreground">{{ fileName }}</div>
                                <div class="text-xs text-muted-foreground">Click or drop another file to replace</div>
                            </div>

                            <div v-else class="flex flex-col items-center gap-2">
                                <div class="h-12 w-12 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                    <Upload class="h-6 w-6" />
                                </div>
                                <div class="text-sm font-semibold text-foreground">
                                    Click to browse or drag and drop your CSV file here
                                </div>
                                <div class="text-xs text-muted-foreground">Maximum file size: 2MB (.csv format)</div>
                            </div>
                        </div>
                        <InputError :message="form.errors.file" />

                        <!-- Section Target Selection -->
                        <div class="space-y-2 max-w-md">
                            <Label for="section_id" class="text-sm font-medium">
                                Assign to Class Section (Optional)
                            </Label>
                            <select
                                id="section_id"
                                v-model="form.section_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option value="">Do not assign yet (Unassigned Roster)</option>
                                <option v-for="sec in sections" :key="sec.id" :value="sec.id">
                                    {{ sec.name }}
                                </option>
                            </select>
                            <p class="text-xs text-muted-foreground">
                                All imported learners will be enrolled directly into this section if selected.
                            </p>
                            <InputError :message="form.errors.section_id" />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <Link
                                href="/admin/users"
                                class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                            >
                                Cancel
                            </Link>
                            <Button type="submit" :disabled="!form.file || form.processing">
                                <Spinner v-if="form.processing" class="mr-2" />
                                <Upload class="mr-2 h-4 w-4" v-else />
                                <span>{{ form.processing ? 'Importing Students...' : 'Process CSV Import' }}</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Results Section -->
            <div v-if="results" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-foreground flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        Import Processing Summary
                    </h2>
                </div>

                <!-- KPI Badges / Tabs -->
                <div class="flex flex-wrap items-center gap-2 border-b border-border pb-3">
                    <button
                        type="button"
                        @click="activeTab = 'created'"
                        :class="[
                            'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors',
                            activeTab === 'created'
                                ? 'bg-emerald-500 text-white shadow-sm'
                                : 'bg-emerald-500/10 text-emerald-700 hover:bg-emerald-500/20'
                        ]"
                    >
                        <CheckCircle2 class="h-4 w-4" />
                        <span>Successfully Created ({{ results.created.length }})</span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'skipped'"
                        :class="[
                            'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors',
                            activeTab === 'skipped'
                                ? 'bg-amber-500 text-white shadow-sm'
                                : 'bg-amber-500/10 text-amber-700 hover:bg-amber-500/20'
                        ]"
                    >
                        <AlertTriangle class="h-4 w-4" />
                        <span>Skipped / Duplicates ({{ results.skipped.length }})</span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'failed'"
                        :class="[
                            'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors',
                            activeTab === 'failed'
                                ? 'bg-destructive text-destructive-foreground shadow-sm'
                                : 'bg-destructive/10 text-destructive hover:bg-destructive/20'
                        ]"
                    >
                        <XCircle class="h-4 w-4" />
                        <span>Failed Validation ({{ results.failed.length }})</span>
                    </button>
                </div>

                <!-- Tab 1: Created -->
                <Card v-if="activeTab === 'created'" class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-border bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">CSV Row</th>
                                    <th class="px-4 py-3">LRN (Credential)</th>
                                    <th class="px-4 py-3">Student Name</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="item in results.created" :key="item.lrn" class="hover:bg-muted/30">
                                    <td class="px-4 py-2.5 text-xs font-mono text-muted-foreground">#{{ item.row }}</td>
                                    <td class="px-4 py-2.5 text-xs font-mono font-medium text-foreground">{{ item.lrn }}</td>
                                    <td class="px-4 py-2.5 text-xs font-medium text-foreground">{{ item.name }}</td>
                                    <td class="px-4 py-2.5">
                                        <Badge variant="default" class="bg-emerald-600 hover:bg-emerald-700 text-xs">
                                            Created
                                        </Badge>
                                    </td>
                                </tr>
                                <tr v-if="results.created.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-xs text-muted-foreground">
                                        No student records were created in this run.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Tab 2: Skipped -->
                <Card v-if="activeTab === 'skipped'" class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-border bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">CSV Row</th>
                                    <th class="px-4 py-3">LRN</th>
                                    <th class="px-4 py-3">Student Name</th>
                                    <th class="px-4 py-3">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="item in results.skipped" :key="item.lrn" class="hover:bg-muted/30">
                                    <td class="px-4 py-2.5 text-xs font-mono text-muted-foreground">#{{ item.row }}</td>
                                    <td class="px-4 py-2.5 text-xs font-mono font-medium text-foreground">{{ item.lrn }}</td>
                                    <td class="px-4 py-2.5 text-xs font-medium text-foreground">{{ item.name }}</td>
                                    <td class="px-4 py-2.5 text-xs text-amber-600 font-medium">
                                        {{ item.reason }}
                                    </td>
                                </tr>
                                <tr v-if="results.skipped.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-xs text-muted-foreground">
                                        No duplicate records were skipped.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Tab 3: Failed -->
                <Card v-if="activeTab === 'failed'" class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-border bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">CSV Row</th>
                                    <th class="px-4 py-3">LRN</th>
                                    <th class="px-4 py-3">Student Name</th>
                                    <th class="px-4 py-3">Errors</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="item in results.failed" :key="item.row" class="hover:bg-muted/30">
                                    <td class="px-4 py-2.5 text-xs font-mono text-muted-foreground">#{{ item.row }}</td>
                                    <td class="px-4 py-2.5 text-xs font-mono font-medium text-foreground">{{ item.lrn || '—' }}</td>
                                    <td class="px-4 py-2.5 text-xs font-medium text-foreground">{{ item.name }}</td>
                                    <td class="px-4 py-2.5 text-xs text-destructive">
                                        <ul class="list-disc list-inside space-y-0.5">
                                            <li v-for="(err, idx) in item.errors" :key="idx">{{ err }}</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr v-if="results.failed.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-xs text-muted-foreground">
                                        Zero row-level errors encountered!
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>
    </div>
</template>
