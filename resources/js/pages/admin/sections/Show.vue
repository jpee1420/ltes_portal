<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useInitials } from '@/composables/useInitials';
import { ArrowLeft, UserPlus, UserMinus, Users, GraduationCap, School, AlertTriangle, CheckCircle2 } from '@lucide/vue';

const { getInitials } = useInitials();

const props = defineProps<{
    section: {
        id: number;
        name: string;
        max_students: number | null;
        enrolled_count?: number;
        is_at_capacity?: boolean;
        school_year: { label: string; is_active: boolean };
        grade_level: { name: string };
        adviser?: { name: string; credential: string } | null;
        student_profiles: Array<{
            id: number;
            lrn: string;
            first_name: string;
            last_name: string;
            gender: string;
            birth_date: string;
            user: { name: string; avatar?: string | null };
        }>;
    };
    unassignedStudents: Array<{
        id: number;
        lrn: string;
        first_name: string;
        last_name: string;
        gender: string;
        user: { name: string; avatar?: string | null };
    }>;
}>();

const selectedStudentId = ref<string | number>('');

const assignForm = useForm({
    student_profile_id: '',
});

const enrolledCount = computed(() => props.section.student_profiles.length);
const maxStudents = computed(() => props.section.max_students);
const isFull = computed(() => maxStudents.value !== null && maxStudents.value > 0 && enrolledCount.value >= maxStudents.value);
const occupancyPercent = computed(() => {
    if (!maxStudents.value) return null;
    return Math.min(100, Math.round((enrolledCount.value / maxStudents.value) * 100));
});

const enrollStudent = () => {
    if (!selectedStudentId.value) return;
    assignForm.student_profile_id = String(selectedStudentId.value);
    assignForm.post(`/admin/sections/${props.section.id}/assign-student`, {
        preserveScroll: true,
        onSuccess: () => {
            selectedStudentId.value = '';
        },
    });
};

const removeStudent = (student: { id: number; user: { name: string } }) => {
    if (confirm(`Remove "${student.user.name}" from this section? The student will become unassigned.`)) {
        router.delete(`/admin/sections/${props.section.id}/students/${student.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div>
        <Head :title="`Section ${section.name} Roster — Admin`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/sections"
                        class="inline-flex items-center justify-center rounded-lg border border-border bg-background p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        aria-label="Back to Sections"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ section.name }}</h1>
                            <span class="inline-flex items-center rounded-md bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                                {{ section.grade_level.name }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            S.Y. {{ section.school_year.label }} &bull; Adviser: <strong class="text-foreground font-medium">{{ section.adviser?.name || 'Unassigned' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link :href="`/admin/sections/${section.id}/edit`">
                        <Button variant="outline" size="sm">Edit Section</Button>
                    </Link>
                </div>
            </div>

            <!-- Capacity Progress / Warning Bar -->
            <Card v-if="maxStudents" class="rounded-xl border border-border/70 shadow-sm p-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-foreground flex items-center gap-1.5">
                            <Users class="h-4 w-4 text-primary" />
                            Section Capacity Occupancy
                        </span>
                        <span :class="isFull ? 'font-bold text-amber-600 dark:text-amber-400' : 'font-medium text-muted-foreground'">
                            {{ enrolledCount }} / {{ maxStudents }} Students ({{ occupancyPercent }}%)
                        </span>
                    </div>
                    <div class="w-full bg-muted rounded-full h-2.5 overflow-hidden">
                        <div
                            class="h-2.5 rounded-full transition-all duration-300"
                            :class="[
                                isFull
                                    ? 'bg-amber-500'
                                    : 'bg-primary'
                            ]"
                            :style="{ width: `${occupancyPercent}%` }"
                        />
                    </div>
                    <div v-if="isFull" class="flex items-center gap-1.5 text-xs text-amber-700 dark:text-amber-300 font-medium pt-1">
                        <AlertTriangle class="h-4 w-4 shrink-0 text-amber-600" />
                        <span>This section has reached its maximum recommended capacity of {{ maxStudents }} learners. Administrators can still enroll additional learners if needed.</span>
                    </div>
                </div>
            </Card>

            <!-- Enrollment Card -->
            <Card class="rounded-xl border border-border/70 shadow-sm">
                <CardHeader class="pb-3">
                    <CardTitle class="text-base font-semibold flex items-center gap-2">
                        <UserPlus class="h-4 w-4 text-primary" />
                        Enroll Student in this Section
                    </CardTitle>
                    <CardDescription>
                        Select from unassigned students to add them to this section roster.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="enrollStudent" class="flex flex-col sm:flex-row items-center gap-3">
                        <select
                            v-model="selectedStudentId"
                            class="flex h-10 w-full sm:flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <option value="">-- Select an unassigned student --</option>
                            <option v-for="student in unassignedStudents" :key="student.id" :value="student.id">
                                {{ student.user.name }} (LRN: {{ student.lrn }}) &mdash; {{ student.gender === 'M' ? 'Male' : 'Female' }}
                            </option>
                        </select>

                        <Button type="submit" :disabled="!selectedStudentId || assignForm.processing" class="w-full sm:w-auto gap-2">
                            <UserPlus class="h-4 w-4" />
                            Enroll Student
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Class Roster Table -->
            <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                <CardHeader class="pb-3 border-b border-border bg-muted/20">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        Class Roster ({{ section.student_profiles.length }} enrolled)
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                <tr>
                                    <th class="px-6 py-3.5">#</th>
                                    <th class="px-6 py-3.5">Student Name</th>
                                    <th class="px-6 py-3.5">LRN</th>
                                    <th class="px-6 py-3.5">Gender</th>
                                    <th class="px-6 py-3.5">Birth Date</th>
                                    <th class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-if="section.student_profiles.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                        <GraduationCap class="mx-auto h-8 w-8 mb-2 opacity-40" />
                                        No students enrolled in this section yet. Use the selector above to enroll students.
                                    </td>
                                </tr>
                                <tr v-for="(student, index) in section.student_profiles" :key="student.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-3.5 text-xs text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="px-6 py-3.5 font-medium text-foreground">
                                        <div class="flex items-center gap-2.5">
                                            <Avatar class="h-7 w-7 rounded-full border border-border">
                                                <AvatarImage
                                                    v-if="student.user.avatar"
                                                    :src="student.user.avatar"
                                                    :alt="student.user.name"
                                                    class="object-cover"
                                                />
                                                <AvatarFallback class="bg-primary/10 text-primary font-bold text-[10px]">
                                                    {{ getInitials(student.user.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <span>{{ student.user.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-xs text-muted-foreground">{{ student.lrn }}</td>
                                    <td class="px-6 py-3.5 text-xs">{{ student.gender === 'M' ? 'Male' : 'Female' }}</td>
                                    <td class="px-6 py-3.5 text-xs text-muted-foreground">{{ student.birth_date }}</td>
                                    <td class="px-6 py-3.5 text-right">
                                        <button
                                            @click="removeStudent(student)"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-destructive hover:underline"
                                            title="Unassign student"
                                        >
                                            <UserMinus class="h-3.5 w-3.5" />
                                            Remove
                                        </button>
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
