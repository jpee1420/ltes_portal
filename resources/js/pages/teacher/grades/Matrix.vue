<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { TableProperties, CheckCircle, AlertTriangle, Save, GraduationCap } from '@lucide/vue';

const props = defineProps<{
    section: {
        id: number;
        name: string;
        school_year_id: number;
        school_year: { label: string };
        grade_level: { name: string };
    } | null;
    subjects: Array<{ id: number; name: string; order: number }>;
    selectedSubject: { id: number; name: string; order: number } | null;
    selectedQuarter: string;
    students: Array<{
        id: number;
        name: string;
        lrn: string;
        gender: string;
        grades: {
            Q1: number | null;
            Q2: number | null;
            Q3: number | null;
            Q4: number | null;
        };
    }>;
}>();

const currentQuarter = ref(props.selectedQuarter || 'Q1');
const currentSubjectId = ref(props.selectedSubject?.id || props.subjects[0]?.id || '');

// Local editable grades map: studentId -> score
const scores = reactive<Record<number, number | string>>({});

// Initialize scores from props
const initScores = () => {
    props.students.forEach((student) => {
        const val = student.grades[currentQuarter.value as keyof typeof student.grades];
        scores[student.id] = val !== null && val !== undefined ? val : '';
    });
};
initScores();

const onFilterChange = () => {
    router.get(
        '/teacher/grades',
        {
            subject_id: currentSubjectId.value,
            quarter: currentQuarter.value,
        },
        {
            preserveState: false,
        }
    );
};

const form = useForm({
    grades: [] as Array<{
        student_id: number;
        subject_id: number;
        school_year_id: number;
        quarter: string;
        score: number;
    }>,
});

const saveGrades = () => {
    if (!props.section || !props.selectedSubject) return;

    const gradesPayload = props.students
        .filter((student) => scores[student.id] !== '' && scores[student.id] !== null)
        .map((student) => ({
            student_id: student.id,
            subject_id: props.selectedSubject!.id,
            school_year_id: props.section!.school_year_id,
            quarter: currentQuarter.value,
            score: Number(scores[student.id]),
        }));

    if (gradesPayload.length === 0) {
        alert('Please enter at least one grade score before saving.');
        return;
    }

    form.grades = gradesPayload;
    form.post('/teacher/grades', {
        preserveScroll: true,
        onSuccess: () => {
            // Updated
        },
    });
};

const calculateAverage = (studentGrades: { Q1: number | null; Q2: number | null; Q3: number | null; Q4: number | null }) => {
    const validScores = [studentGrades.Q1, studentGrades.Q2, studentGrades.Q3, studentGrades.Q4].filter((s): s is number => s !== null);
    if (validScores.length === 0) return '—';
    const sum = validScores.reduce((acc, curr) => acc + curr, 0);
    return (sum / validScores.length).toFixed(1);
};
</script>

<template>
    <div>
        <Head title="Grade Encoding Matrix — Teacher Portal" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <div v-if="!section" class="flex flex-col items-center justify-center py-16 text-center">
                <div class="rounded-full bg-muted p-4 mb-4">
                    <GraduationCap class="h-10 w-10 text-muted-foreground" />
                </div>
                <h2 class="text-xl font-bold text-foreground">No Advisory Section Assigned</h2>
                <p class="text-sm text-muted-foreground max-w-md mt-1">
                    You have not been assigned to a section as class adviser yet. Please contact the school administrator to encode grades.
                </p>
            </div>

            <div v-else class="space-y-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">Quarterly Grade Matrix</h1>
                            <span class="inline-flex items-center rounded-md bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                                {{ section.grade_level.name }} &bull; {{ section.name }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Encode and update quarterly subject grades. Passing mark is <strong class="text-foreground">75</strong>.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button @click="saveGrades" :disabled="form.processing" class="gap-2 shadow-sm">
                            <Spinner v-if="form.processing" class="h-4 w-4" />
                            <Save v-else class="h-4 w-4" />
                            <span>{{ form.processing ? 'Saving...' : 'Save Quarterly Grades' }}</span>
                        </Button>
                    </div>
                </div>

                <!-- Filters / Subject & Quarter selector -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted-foreground">Select Subject</label>
                        <select
                            v-model="currentSubjectId"
                            @change="onFilterChange"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <option v-for="subj in subjects" :key="subj.id" :value="subj.id">
                                {{ subj.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted-foreground">Select Grading Period (Quarter)</label>
                        <div class="flex rounded-md border border-input bg-muted/40 p-0.5">
                            <button
                                v-for="q in ['Q1', 'Q2', 'Q3', 'Q4']"
                                :key="q"
                                type="button"
                                @click="currentQuarter = q; onFilterChange()"
                                :class="[
                                    'flex-1 py-1.5 text-xs font-semibold rounded transition-all',
                                    currentQuarter === q
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground'
                                ]"
                            >
                                {{ q }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grade Matrix Table -->
                <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                    <CardHeader class="pb-3 border-b border-border bg-muted/20 flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <TableProperties class="h-5 w-5 text-primary" />
                                <span>{{ selectedSubject?.name || 'Subject' }} &mdash; {{ currentQuarter }}</span>
                            </CardTitle>
                            <CardDescription>
                                Enter raw score (60 - 100). Scores below 75 are flagged as failing.
                            </CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                    <tr>
                                        <th class="px-6 py-3.5 w-12">#</th>
                                        <th class="px-6 py-3.5">Learner Name</th>
                                        <th class="px-6 py-3.5">LRN</th>
                                        <th class="px-6 py-3.5 text-center w-36">
                                            {{ currentQuarter }} Score
                                        </th>
                                        <th class="px-6 py-3.5 text-center w-28">Remarks</th>
                                        <th class="px-6 py-3.5 text-center w-28">Subject Avg</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="students.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                            No students currently enrolled in this section.
                                        </td>
                                    </tr>
                                    <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-6 py-3 text-xs text-muted-foreground">{{ index + 1 }}</td>
                                        <td class="px-6 py-3 font-semibold text-foreground">{{ student.name }}</td>
                                        <td class="px-6 py-3 font-mono text-xs text-muted-foreground">{{ student.lrn }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <input
                                                v-model="scores[student.id]"
                                                type="number"
                                                min="60"
                                                max="100"
                                                placeholder="60-100"
                                                class="w-24 text-center h-9 font-mono text-sm rounded-md border bg-background px-2 py-1 shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2"
                                                :class="[
                                                    scores[student.id] !== '' && Number(scores[student.id]) < 75
                                                        ? 'border-destructive/80 text-destructive bg-destructive/10 focus-visible:ring-destructive'
                                                        : 'border-input focus-visible:ring-ring'
                                                ]"
                                            />
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span
                                                v-if="scores[student.id] !== '' && scores[student.id] !== null"
                                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                                :class="[
                                                    Number(scores[student.id]) >= 75
                                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                                        : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'
                                                ]"
                                            >
                                                <CheckCircle v-if="Number(scores[student.id]) >= 75" class="h-3 w-3" />
                                                <AlertTriangle v-else class="h-3 w-3" />
                                                {{ Number(scores[student.id]) >= 75 ? 'Passed' : 'Failed' }}
                                            </span>
                                            <span v-else class="text-xs text-muted-foreground italic">Pending</span>
                                        </td>
                                        <td class="px-6 py-3 text-center font-mono text-xs font-medium text-muted-foreground">
                                            {{ calculateAverage(student.grades) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
