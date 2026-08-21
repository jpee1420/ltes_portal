<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Award, BookOpen, GraduationCap, CheckCircle2, AlertCircle, FileText } from '@lucide/vue';

const props = defineProps<{
    reportCard: {
        student: {
            id: number;
            name: string;
            lrn: string;
            gender: string;
            section: string | null;
            grade_level: string | null;
        };
        school_year: {
            id: number;
            label: string;
        };
        subjects: Array<{
            subject_id: number;
            name: string;
            Q1: number | null;
            Q2: number | null;
            Q3: number | null;
            Q4: number | null;
            final_rating: number | null;
            is_passed: boolean | null;
            remarks: string;
        }>;
        quarterly_averages: {
            Q1: number | null;
            Q2: number | null;
            Q3: number | null;
            Q4: number | null;
        };
        general_average: number | null;
        descriptor: string;
    } | null;
    schoolYears: Array<{ id: number; label: string; is_active: boolean }>;
    selectedSchoolYearId: number | null;
}>();

const onYearChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    router.get(
        '/student/grades',
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
        <Head title="Learner Progress Report Card — Portal" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-5xl mx-auto">
            <!-- Top Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
                        <FileText class="h-6 w-6 text-primary" />
                        Learner Progress Report Card
                    </h1>
                    <p class="text-sm text-muted-foreground">Official Quarterly Academic Performance &amp; Grades (DepEd Form 138-E).</p>
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

            <div v-if="!reportCard" class="flex flex-col items-center justify-center py-16 text-center">
                <div class="rounded-full bg-muted p-4 mb-4">
                    <GraduationCap class="h-10 w-10 text-muted-foreground" />
                </div>
                <h2 class="text-xl font-bold text-foreground">No Profile Found</h2>
                <p class="text-sm text-muted-foreground max-w-md mt-1">
                    Your student record has not been linked yet. Please contact your school administrator.
                </p>
            </div>

            <div v-else class="space-y-6">
                <!-- Learner Header Badge -->
                <Card class="rounded-xl border border-primary/20 bg-primary/5 shadow-sm">
                    <CardContent class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-muted-foreground font-medium uppercase block">Learner Name</span>
                                <span class="font-bold text-foreground text-base">{{ reportCard.student.name }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-muted-foreground font-medium uppercase block">12-Digit LRN</span>
                                <span class="font-mono font-medium text-foreground">{{ reportCard.student.lrn }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-muted-foreground font-medium uppercase block">Grade Level &amp; Section</span>
                                <span class="font-medium text-foreground">
                                    {{ reportCard.student.grade_level || 'Unassigned' }} &bull; {{ reportCard.student.section || 'No Section' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-muted-foreground font-medium uppercase block">Academic Calendar</span>
                                <span class="font-semibold text-primary">S.Y. {{ reportCard.school_year.label }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Grades Table -->
                <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                    <CardHeader class="pb-3 border-b border-border bg-muted/20">
                        <CardTitle class="text-lg font-semibold flex items-center gap-2">
                            <BookOpen class="h-5 w-5 text-primary" />
                            Report on Learning Progress and Achievement
                        </CardTitle>
                        <CardDescription>
                            Passing grade is 75 in all learning areas.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                    <tr>
                                        <th class="px-6 py-3.5">Learning Areas (Subjects)</th>
                                        <th class="px-4 py-3.5 text-center w-20">Q1</th>
                                        <th class="px-4 py-3.5 text-center w-20">Q2</th>
                                        <th class="px-4 py-3.5 text-center w-20">Q3</th>
                                        <th class="px-4 py-3.5 text-center w-20">Q4</th>
                                        <th class="px-4 py-3.5 text-center w-28 font-bold">Final Rating</th>
                                        <th class="px-6 py-3.5 text-center w-28">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="reportCard.subjects.length === 0">
                                        <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">
                                            No subjects assigned for this grade level.
                                        </td>
                                    </tr>
                                    <tr v-for="subj in reportCard.subjects" :key="subj.subject_id" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-6 py-3.5 font-medium text-foreground">{{ subj.name }}</td>
                                        <td class="px-4 py-3.5 text-center font-mono text-xs">
                                            <span :class="{'text-destructive font-bold': subj.Q1 !== null && subj.Q1 < 75}">
                                                {{ subj.Q1 !== null ? subj.Q1 : '—' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-mono text-xs">
                                            <span :class="{'text-destructive font-bold': subj.Q2 !== null && subj.Q2 < 75}">
                                                {{ subj.Q2 !== null ? subj.Q2 : '—' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-mono text-xs">
                                            <span :class="{'text-destructive font-bold': subj.Q3 !== null && subj.Q3 < 75}">
                                                {{ subj.Q3 !== null ? subj.Q3 : '—' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-mono text-xs">
                                            <span :class="{'text-destructive font-bold': subj.Q4 !== null && subj.Q4 < 75}">
                                                {{ subj.Q4 !== null ? subj.Q4 : '—' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-mono text-sm font-bold bg-muted/20">
                                            <span :class="subj.final_rating !== null && subj.final_rating < 75 ? 'text-destructive' : 'text-foreground'">
                                                {{ subj.final_rating !== null ? subj.final_rating : '—' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 text-center">
                                            <span
                                                v-if="subj.remarks !== 'Pending'"
                                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                                :class="subj.is_passed ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'"
                                            >
                                                <CheckCircle2 v-if="subj.is_passed" class="h-3 w-3" />
                                                <AlertCircle v-else class="h-3 w-3" />
                                                {{ subj.remarks }}
                                            </span>
                                            <span v-else class="text-xs text-muted-foreground italic">Pending</span>
                                        </td>
                                    </tr>
                                </tbody>
                                <!-- General Average Summary Footer -->
                                <tfoot class="border-t-2 border-border bg-muted/40 font-bold">
                                    <tr>
                                        <td class="px-6 py-4 text-foreground uppercase tracking-wide text-xs">General Average</td>
                                        <td class="px-4 py-4 text-center font-mono text-xs text-muted-foreground">{{ reportCard.quarterly_averages.Q1 ?? '—' }}</td>
                                        <td class="px-4 py-4 text-center font-mono text-xs text-muted-foreground">{{ reportCard.quarterly_averages.Q2 ?? '—' }}</td>
                                        <td class="px-4 py-4 text-center font-mono text-xs text-muted-foreground">{{ reportCard.quarterly_averages.Q3 ?? '—' }}</td>
                                        <td class="px-4 py-4 text-center font-mono text-xs text-muted-foreground">{{ reportCard.quarterly_averages.Q4 ?? '—' }}</td>
                                        <td class="px-4 py-4 text-center font-mono text-base font-extrabold text-primary bg-primary/5">
                                            {{ reportCard.general_average ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-xs">
                                            <span v-if="reportCard.general_average !== null" :class="reportCard.general_average >= 75 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-destructive font-bold'">
                                                {{ reportCard.general_average >= 75 ? 'PASSED' : 'FAILED' }}
                                            </span>
                                            <span v-else class="text-muted-foreground italic">—</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Grading Scale & Descriptors -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Card class="rounded-xl border border-border/70 shadow-sm">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <Award class="h-4 w-4 text-primary" />
                                Overall Descriptor
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/40 border border-border">
                                <span class="text-sm font-medium text-muted-foreground">DepEd Performance Rating:</span>
                                <span class="text-sm font-bold text-foreground">{{ reportCard.descriptor }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="rounded-xl border border-border/70 shadow-sm">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold">DepEd Grading Scale Legend</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-muted-foreground">
                                <div><strong class="text-foreground">90 &ndash; 100:</strong> Outstanding (O)</div>
                                <div><strong class="text-foreground">85 &ndash; 89:</strong> Very Satisfactory (VS)</div>
                                <div><strong class="text-foreground">80 &ndash; 84:</strong> Satisfactory (S)</div>
                                <div><strong class="text-foreground">75 &ndash; 79:</strong> Fairly Satisfactory (FS)</div>
                                <div class="col-span-2 text-destructive"><strong class="text-destructive">Below 75:</strong> Did Not Meet Expectations (Failing)</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
