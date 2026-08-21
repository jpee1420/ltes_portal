<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { CalendarCheck, CalendarDays, CheckCircle2, XCircle, Clock, AlertCircle } from '@lucide/vue';

const props = defineProps<{
    student: {
        id: number;
        name: string;
        lrn: string;
        section: string | null;
        grade_level: string | null;
    };
    school_year: {
        id: number;
        label: string;
    };
    summary: {
        total_days: number;
        present_count: number;
        absent_count: number;
        tardy_count: number;
        excused_count: number;
        attendance_rate: number;
    };
    records: Array<{
        id: number;
        date: string;
        status: 'present' | 'absent' | 'tardy' | 'excused';
        remarks: string | null;
    }>;
    schoolYears: Array<{ id: number; label: string; is_active: boolean }>;
    selectedSchoolYearId: number | null;
}>();

const onYearChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    router.get(
        '/student/attendance',
        {
            school_year_id: target.value,
        },
        {
            preserveState: false,
        }
    );
};
</script>

<template>
    <div>
        <Head title="Attendance Record — Student Portal" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-5xl mx-auto">
            <!-- Top Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
                        <CalendarCheck class="h-6 w-6 text-primary" />
                        Daily Attendance Record
                    </h1>
                    <p class="text-sm text-muted-foreground">Homeroom daily presence log and cumulative attendance summary.</p>
                </div>

                <div v-if="schoolYears.length > 0" class="flex items-center gap-2">
                    <label class="text-xs font-medium text-muted-foreground whitespace-nowrap">School Year:</label>
                    <select
                        :value="selectedSchoolYearId"
                        @change="onYearChange"
                        class="flex h-9 rounded-md border border-input bg-background px-3 py-1 text-xs font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    >
                        <option v-for="sy in schoolYears" :key="sy.id" :value="sy.id">
                            S.Y. {{ sy.label }} {{ sy.is_active ? '(Active)' : '' }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <Card class="rounded-xl border border-primary/20 bg-primary/5 shadow-sm p-4">
                    <span class="text-xs text-muted-foreground font-medium block">Attendance Rate</span>
                    <span class="text-2xl font-extrabold text-primary mt-1 block">{{ summary.attendance_rate }}%</span>
                    <span class="text-[10px] text-muted-foreground mt-0.5 block">{{ summary.total_days }} total school days</span>
                </Card>

                <Card class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 shadow-sm p-4">
                    <span class="text-xs text-emerald-800 dark:text-emerald-300 font-medium block">Days Present</span>
                    <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block">{{ summary.present_count }}</span>
                    <span class="text-[10px] text-muted-foreground mt-0.5 block">Full attendance</span>
                </Card>

                <Card class="rounded-xl border border-rose-500/20 bg-rose-500/5 shadow-sm p-4">
                    <span class="text-xs text-rose-800 dark:text-rose-300 font-medium block">Days Absent</span>
                    <span class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1 block">{{ summary.absent_count }}</span>
                    <span class="text-[10px] text-muted-foreground mt-0.5 block">Unexcused absence</span>
                </Card>

                <Card class="rounded-xl border border-amber-500/20 bg-amber-500/5 shadow-sm p-4">
                    <span class="text-xs text-amber-800 dark:text-amber-300 font-medium block">Days Tardy</span>
                    <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 block">{{ summary.tardy_count }}</span>
                    <span class="text-[10px] text-muted-foreground mt-0.5 block">Late arrival</span>
                </Card>

                <Card class="rounded-xl border border-blue-500/20 bg-blue-500/5 shadow-sm p-4 col-span-2 sm:col-span-1">
                    <span class="text-xs text-blue-800 dark:text-blue-300 font-medium block">Days Excused</span>
                    <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1 block">{{ summary.excused_count }}</span>
                    <span class="text-[10px] text-muted-foreground mt-0.5 block">Valid excuse/health</span>
                </Card>
            </div>

            <!-- Attendance Daily Table -->
            <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                <CardHeader class="pb-3 border-b border-border bg-muted/20">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <CalendarDays class="h-5 w-5 text-primary" />
                        Daily Attendance History (S.Y. {{ school_year.label }})
                    </CardTitle>
                    <CardDescription>
                        Complete daily log recorded by your homeroom class adviser.
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                <tr>
                                    <th class="px-6 py-3.5">Date</th>
                                    <th class="px-6 py-3.5">Attendance Status</th>
                                    <th class="px-6 py-3.5">Adviser Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-if="records.length === 0">
                                    <td colspan="3" class="px-6 py-12 text-center text-muted-foreground">
                                        No daily attendance records recorded for this academic year yet.
                                    </td>
                                </tr>
                                <tr v-for="rec in records" :key="rec.id" class="hover:bg-muted/20 transition-colors">
                                    <td class="px-6 py-3.5 font-medium text-foreground">
                                        {{ rec.date }}
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                            :class="{
                                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300': rec.status === 'present',
                                                'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300': rec.status === 'absent',
                                                'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300': rec.status === 'tardy',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300': rec.status === 'excused',
                                            }"
                                        >
                                            <CheckCircle2 v-if="rec.status === 'present'" class="h-3 w-3" />
                                            <XCircle v-else-if="rec.status === 'absent'" class="h-3 w-3" />
                                            <Clock v-else-if="rec.status === 'tardy'" class="h-3 w-3" />
                                            <AlertCircle v-else class="h-3 w-3" />
                                            {{ rec.status.charAt(0).toUpperCase() + rec.status.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-muted-foreground">
                                        {{ rec.remarks || '—' }}
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
