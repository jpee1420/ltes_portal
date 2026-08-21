<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar, Plus, CheckCircle2, Circle } from '@lucide/vue';

defineProps<{
    schoolYears: Array<{
        id: number;
        label: string;
        is_active: boolean;
        start_date: string;
        end_date: string;
        sections_count: number;
    }>;
}>();

const activateYear = (sy: { id: number; label: string }) => {
    if (confirm(`Are you sure you want to set S.Y. ${sy.label} as the active school year? This will affect section assignments, grading, and attendance portals.`)) {
        router.post(`/admin/school-years/${sy.id}/activate`, {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div>
        <Head title="School Years — Admin" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">School Year Management</h1>
                    <p class="text-sm text-muted-foreground">Manage academic calendars and configure the currently active academic cycle.</p>
                </div>
                <div>
                    <Link href="/admin/school-years/create">
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" />
                            Add School Year
                        </Button>
                    </Link>
                </div>
            </div>

            <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                <CardHeader class="pb-3 border-b border-border bg-muted/20">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <Calendar class="h-5 w-5 text-primary" />
                        Academic Years
                    </CardTitle>
                    <CardDescription>
                        Only one school year can be active at a time. Active school years govern live grading and attendance records.
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                <tr>
                                    <th class="px-6 py-3.5">School Year</th>
                                    <th class="px-6 py-3.5">Status</th>
                                    <th class="px-6 py-3.5">Duration</th>
                                    <th class="px-6 py-3.5 text-center">Sections</th>
                                    <th class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="sy in schoolYears" :key="sy.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 font-bold text-foreground">
                                        S.Y. {{ sy.label }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            v-if="sy.is_active"
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-950 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:text-emerald-300"
                                        >
                                            <CheckCircle2 class="h-3.5 w-3.5 text-emerald-600" />
                                            Active Academic Year
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground"
                                        >
                                            <Circle class="h-3 w-3 opacity-40" />
                                            Inactive
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-muted-foreground">
                                        {{ sy.start_date }} to {{ sy.end_date }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-xs font-medium text-foreground">
                                        {{ sy.sections_count }} sections
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Button
                                            v-if="!sy.is_active"
                                            @click="activateYear(sy)"
                                            variant="outline"
                                            size="sm"
                                            class="text-xs"
                                        >
                                            Set as Active
                                        </Button>
                                        <span v-else class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            Current Active
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
