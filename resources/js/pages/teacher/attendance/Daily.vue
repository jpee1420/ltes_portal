<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { CalendarDays, Save, CheckCheck, GraduationCap } from '@lucide/vue';

const props = defineProps<{
    section: {
        id: number;
        name: string;
        school_year: { label: string };
        grade_level: { name: string };
    } | null;
    date: string;
    students: Array<{
        id: number;
        name: string;
        lrn: string;
        gender: string;
        status: 'present' | 'absent' | 'tardy' | 'excused';
        remarks: string;
    }>;
}>();

const selectedDate = ref(props.date);

// Local state for attendance records
const attendanceMap = reactive<Record<number, { status: 'present' | 'absent' | 'tardy' | 'excused'; remarks: string }>>({});

const initMap = () => {
    props.students.forEach((s) => {
        attendanceMap[s.id] = {
            status: s.status || 'present',
            remarks: s.remarks || '',
        };
    });
};
initMap();

const onDateChange = () => {
    router.get(
        '/teacher/attendance',
        {
            date: selectedDate.value,
        },
        {
            preserveState: false,
        }
    );
};

const markAllPresent = () => {
    props.students.forEach((s) => {
        attendanceMap[s.id].status = 'present';
    });
};

const form = useForm({
    date: props.date,
    records: [] as Array<{
        student_id: number;
        status: 'present' | 'absent' | 'tardy' | 'excused';
        remarks?: string;
    }>,
});

const saveAttendance = () => {
    form.date = selectedDate.value;
    form.records = props.students.map((s) => ({
        student_id: s.id,
        status: attendanceMap[s.id].status,
        remarks: attendanceMap[s.id].remarks || undefined,
    }));

    form.post('/teacher/attendance', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <Head title="Daily Attendance — Teacher Portal" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <div v-if="!section" class="flex flex-col items-center justify-center py-16 text-center">
                <div class="rounded-full bg-muted p-4 mb-4">
                    <GraduationCap class="h-10 w-10 text-muted-foreground" />
                </div>
                <h2 class="text-xl font-bold text-foreground">No Advisory Section Assigned</h2>
                <p class="text-sm text-muted-foreground max-w-md mt-1">
                    You have not been assigned to a section as class adviser yet. Please contact the school administrator to record attendance.
                </p>
            </div>

            <div v-else class="space-y-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">Daily Attendance</h1>
                            <span class="inline-flex items-center rounded-md bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                                {{ section.grade_level.name }} &bull; {{ section.name }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Record daily attendance for your homeroom advisory class.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Button variant="outline" @click="markAllPresent" class="gap-2 text-xs">
                            <CheckCheck class="h-4 w-4 text-emerald-600" />
                            Mark All Present
                        </Button>
                        <Button @click="saveAttendance" :disabled="form.processing" class="gap-2 shadow-sm">
                            <Spinner v-if="form.processing" class="h-4 w-4" />
                            <Save v-else class="h-4 w-4" />
                            <span>{{ form.processing ? 'Saving...' : 'Save Attendance' }}</span>
                        </Button>
                    </div>
                </div>

                <!-- Date Selector bar -->
                <div class="flex flex-col sm:flex-row items-center gap-3 bg-muted/40 p-4 rounded-xl border border-border">
                    <label class="text-sm font-medium text-foreground flex items-center gap-2">
                        <CalendarDays class="h-4 w-4 text-primary" />
                        Attendance Date:
                    </label>
                    <input
                        v-model="selectedDate"
                        type="date"
                        @change="onDateChange"
                        class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    />
                </div>

                <!-- Attendance Table -->
                <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                    <CardHeader class="pb-3 border-b border-border bg-muted/20">
                        <CardTitle class="text-lg font-semibold flex items-center gap-2">
                            <CalendarDays class="h-5 w-5 text-primary" />
                            Attendance Roster ({{ date }})
                        </CardTitle>
                        <CardDescription>
                            Select the status for each learner and add optional remarks if absent or tardy.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                    <tr>
                                        <th class="px-6 py-3.5 w-12">#</th>
                                        <th class="px-6 py-3.5">Learner Name</th>
                                        <th class="px-6 py-3.5">LRN</th>
                                        <th class="px-6 py-3.5 text-center">Status</th>
                                        <th class="px-6 py-3.5">Remarks / Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="students.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                            No students currently enrolled in this section.
                                        </td>
                                    </tr>
                                    <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-muted/20 transition-colors">
                                        <td class="px-6 py-3.5 text-xs text-muted-foreground">{{ index + 1 }}</td>
                                        <td class="px-6 py-3.5 font-semibold text-foreground">{{ student.name }}</td>
                                        <td class="px-6 py-3.5 font-mono text-xs text-muted-foreground">{{ student.lrn }}</td>
                                        <td class="px-6 py-3.5">
                                            <div class="flex items-center justify-center gap-1">
                                                <button
                                                    type="button"
                                                    @click="attendanceMap[student.id].status = 'present'"
                                                    :class="[
                                                        'px-2.5 py-1 text-xs font-semibold rounded-md border transition-all',
                                                        attendanceMap[student.id].status === 'present'
                                                            ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm'
                                                            : 'bg-background text-muted-foreground border-border hover:bg-muted'
                                                    ]"
                                                >
                                                    Present
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="attendanceMap[student.id].status = 'absent'"
                                                    :class="[
                                                        'px-2.5 py-1 text-xs font-semibold rounded-md border transition-all',
                                                        attendanceMap[student.id].status === 'absent'
                                                            ? 'bg-rose-600 text-white border-rose-600 shadow-sm'
                                                            : 'bg-background text-muted-foreground border-border hover:bg-muted'
                                                    ]"
                                                >
                                                    Absent
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="attendanceMap[student.id].status = 'tardy'"
                                                    :class="[
                                                        'px-2.5 py-1 text-xs font-semibold rounded-md border transition-all',
                                                        attendanceMap[student.id].status === 'tardy'
                                                            ? 'bg-amber-600 text-white border-amber-600 shadow-sm'
                                                            : 'bg-background text-muted-foreground border-border hover:bg-muted'
                                                    ]"
                                                >
                                                    Tardy
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="attendanceMap[student.id].status = 'excused'"
                                                    :class="[
                                                        'px-2.5 py-1 text-xs font-semibold rounded-md border transition-all',
                                                        attendanceMap[student.id].status === 'excused'
                                                            ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                                            : 'bg-background text-muted-foreground border-border hover:bg-muted'
                                                    ]"
                                                >
                                                    Excused
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <input
                                                v-model="attendanceMap[student.id].remarks"
                                                type="text"
                                                placeholder="e.g. Fever, Medical reason, Late by 10m"
                                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                            />
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
