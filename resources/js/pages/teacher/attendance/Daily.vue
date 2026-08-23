<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { CalendarDays, Save, CheckCheck, GraduationCap, History, CheckSquare, Square, AlertCircle } from '@lucide/vue';

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
    lastRecordedDate?: string | null;
}>();

const selectedDate = ref(props.date);
const isFetchingCarryForward = ref(false);
const carryForwardNotice = ref<string | null>(null);

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

const allPresent = computed(() => {
    if (props.students.length === 0) return false;
    return props.students.every((s) => attendanceMap[s.id]?.status === 'present');
});

const presentCount = computed(() => {
    return props.students.filter((s) => attendanceMap[s.id]?.status === 'present').length;
});

const absentCount = computed(() => {
    return props.students.filter((s) => attendanceMap[s.id]?.status === 'absent').length;
});

const tardyCount = computed(() => {
    return props.students.filter((s) => attendanceMap[s.id]?.status === 'tardy').length;
});

const excusedCount = computed(() => {
    return props.students.filter((s) => attendanceMap[s.id]?.status === 'excused').length;
});

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

const toggleAllPresent = () => {
    const targetStatus = allPresent.value ? 'absent' : 'present';
    props.students.forEach((s) => {
        attendanceMap[s.id].status = targetStatus;
    });
};

const toggleStudentPresent = (studentId: number) => {
    if (attendanceMap[studentId].status === 'present') {
        attendanceMap[studentId].status = 'absent';
    } else {
        attendanceMap[studentId].status = 'present';
    }
};

const carryForward = async () => {
    isFetchingCarryForward.value = true;
    carryForwardNotice.value = null;

    try {
        const response = await fetch(`/teacher/attendance/carry-forward?before_date=${selectedDate.value}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) throw new Error('Failed to fetch previous attendance');

        const data = await response.json();

        if (data.last_date && data.records) {
            props.students.forEach((s) => {
                if (data.records[s.id]) {
                    attendanceMap[s.id].status = data.records[s.id].status;
                    attendanceMap[s.id].remarks = data.records[s.id].remarks || '';
                }
            });
            carryForwardNotice.value = `Applied attendance records from ${data.last_date}.`;
        } else {
            carryForwardNotice.value = 'No previous recorded attendance found.';
        }
    } catch (e) {
        carryForwardNotice.value = 'Could not carry forward previous records.';
    } finally {
        isFetchingCarryForward.value = false;
    }
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
                            Record and update daily learner attendance with 1-click carry-forward.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            :disabled="isFetchingCarryForward"
                            @click="carryForward"
                            class="gap-1.5 text-xs"
                        >
                            <Spinner v-if="isFetchingCarryForward" class="h-3.5 w-3.5" />
                            <History v-else class="h-3.5 w-3.5 text-primary" />
                            <span>{{ lastRecordedDate ? `Carry Forward (${lastRecordedDate})` : 'Carry Forward Previous Day' }}</span>
                        </Button>

                        <Button @click="saveAttendance" :disabled="form.processing" class="gap-2 shadow-sm">
                            <Spinner v-if="form.processing" class="h-4 w-4" />
                            <Save v-else class="h-4 w-4" />
                            <span>{{ form.processing ? 'Saving...' : 'Save Attendance' }}</span>
                        </Button>
                    </div>
                </div>

                <!-- Date Selector bar & KPI stats -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-muted/40 p-4 rounded-xl border border-border">
                    <div class="flex items-center gap-3">
                        <label for="att_date" class="text-sm font-medium text-foreground flex items-center gap-2">
                            <CalendarDays class="h-4 w-4 text-primary" />
                            Attendance Date:
                        </label>
                        <input
                            id="att_date"
                            v-model="selectedDate"
                            type="date"
                            @change="onDateChange"
                            class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        />
                    </div>

                    <!-- Summary Pills -->
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                            Present: {{ presentCount }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20">
                            Absent: {{ absentCount }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20">
                            Tardy: {{ tardyCount }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-500/20">
                            Excused: {{ excusedCount }}
                        </span>
                    </div>
                </div>

                <!-- Notice Toast -->
                <div v-if="carryForwardNotice" class="flex items-center gap-2 p-3 rounded-lg bg-primary/10 border border-primary/20 text-xs font-medium text-foreground">
                    <CheckCheck class="h-4 w-4 text-primary shrink-0" />
                    <span>{{ carryForwardNotice }}</span>
                </div>

                <!-- Attendance Table Card -->
                <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                    <CardHeader class="pb-3 border-b border-border bg-muted/20 flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                <CalendarDays class="h-5 w-5 text-primary" />
                                Attendance Roster ({{ date }})
                            </CardTitle>
                            <CardDescription>
                                Uncheck absent/tardy learners to specify reasons.
                            </CardDescription>
                        </div>
                        <div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                @click="toggleAllPresent"
                                class="gap-1.5 text-xs font-medium"
                            >
                                <CheckSquare v-if="allPresent" class="h-4 w-4 text-emerald-600" />
                                <Square v-else class="h-4 w-4 text-muted-foreground" />
                                <span>{{ allPresent ? 'Deselect All (Mark All Absent)' : 'Select All (Mark All Present)' }}</span>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                    <tr>
                                        <th class="px-4 py-3.5 text-center w-12">
                                            <input
                                                type="checkbox"
                                                :checked="allPresent"
                                                @change="toggleAllPresent"
                                                class="h-4 w-4 rounded border-border text-primary focus:ring-primary cursor-pointer"
                                                title="Select / Deselect all present"
                                            />
                                        </th>
                                        <th class="px-4 py-3.5">Learner Name</th>
                                        <th class="px-4 py-3.5">LRN</th>
                                        <th class="px-4 py-3.5 text-center">Status</th>
                                        <th class="px-4 py-3.5">Remarks / Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="students.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                            No students currently enrolled in this section.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(student, index) in students"
                                        :key="student.id"
                                        class="transition-colors"
                                        :class="[
                                            attendanceMap[student.id]?.status === 'present'
                                                ? 'hover:bg-emerald-500/5'
                                                : 'bg-muted/30 hover:bg-muted/50'
                                        ]"
                                    >
                                        <!-- Checkbox -->
                                        <td class="px-4 py-3 text-center">
                                            <input
                                                type="checkbox"
                                                :checked="attendanceMap[student.id]?.status === 'present'"
                                                @change="toggleStudentPresent(student.id)"
                                                class="h-4 w-4 rounded border-border text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                            />
                                        </td>

                                        <!-- Name & Present Indicator -->
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-foreground">{{ student.name }}</span>
                                                <span
                                                    v-if="attendanceMap[student.id]?.status === 'present'"
                                                    class="inline-flex items-center text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950 px-1.5 py-0.2 rounded"
                                                >
                                                    Present
                                                </span>
                                            </div>
                                        </td>

                                        <!-- LRN -->
                                        <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                            {{ student.lrn }}
                                        </td>

                                        <!-- Status Buttons (Compact) -->
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-1">
                                                <button
                                                    type="button"
                                                    @click="attendanceMap[student.id].status = 'present'"
                                                    :class="[
                                                        'px-2 py-0.5 text-xs font-semibold rounded border transition-all',
                                                        attendanceMap[student.id]?.status === 'present'
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
                                                        'px-2 py-0.5 text-xs font-semibold rounded border transition-all',
                                                        attendanceMap[student.id]?.status === 'absent'
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
                                                        'px-2 py-0.5 text-xs font-semibold rounded border transition-all',
                                                        attendanceMap[student.id]?.status === 'tardy'
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
                                                        'px-2 py-0.5 text-xs font-semibold rounded border transition-all',
                                                        attendanceMap[student.id]?.status === 'excused'
                                                            ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                                            : 'bg-background text-muted-foreground border-border hover:bg-muted'
                                                    ]"
                                                >
                                                    Excused
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Remarks -->
                                        <td class="px-4 py-3">
                                            <input
                                                v-model="attendanceMap[student.id].remarks"
                                                type="text"
                                                placeholder="e.g. Fever, Medical excuse, Late 15m"
                                                class="flex h-8 w-full rounded-md border border-input bg-background px-2.5 py-1 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
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
