<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useInitials } from '@/composables/useInitials';
import { formatDate } from '@/lib/utils';
import { Users, GraduationCap, TableProperties, CalendarDays } from '@lucide/vue';

const { getInitials } = useInitials();

defineProps<{
    section: {
        id: number;
        name: string;
        school_year: { label: string };
        grade_level: { name: string };
        student_profiles: Array<{
            id: number;
            lrn: string;
            first_name: string;
            last_name: string;
            gender: string;
            birth_date: string;
            user: { name: string; avatar?: string | null };
        }>;
    } | null;
}>();
</script>

<template>
    <div>
        <Head title="Class Roster — Teacher Portal" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <div v-if="!section" class="flex flex-col items-center justify-center py-16 text-center">
                <div class="rounded-full bg-muted p-4 mb-4">
                    <GraduationCap class="h-10 w-10 text-muted-foreground" />
                </div>
                <h2 class="text-xl font-bold text-foreground">No Advisory Section Assigned</h2>
                <p class="text-sm text-muted-foreground max-w-md mt-1 mb-6">
                    You have not been assigned as an adviser to any section for the active school year yet. Please contact the school administrator.
                </p>
            </div>

            <div v-else class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">Class Roster &bull; {{ section.name }}</h1>
                            <span class="inline-flex items-center rounded-md bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                                {{ section.grade_level.name }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Academic S.Y. {{ section.school_year.label }} &bull; Total Enrolled: {{ section.student_profiles.length }} students
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link href="/teacher/grades">
                            <Button class="gap-2">
                                <TableProperties class="h-4 w-4" />
                                Encode Grades
                            </Button>
                        </Link>
                        <Link href="/teacher/attendance">
                            <Button variant="outline" class="gap-2">
                                <CalendarDays class="h-4 w-4" />
                                Attendance
                            </Button>
                        </Link>
                    </div>
                </div>

                <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                    <CardHeader class="pb-3 border-b border-border bg-muted/20">
                        <CardTitle class="text-lg font-semibold flex items-center gap-2">
                            <Users class="h-5 w-5 text-primary" />
                            Official Learner Roster
                        </CardTitle>
                        <CardDescription>
                            All learners officially assigned to your homeroom class.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                    <tr>
                                        <th class="px-6 py-3.5">#</th>
                                        <th class="px-6 py-3.5">Learner Name</th>
                                        <th class="px-6 py-3.5">12-Digit LRN</th>
                                        <th class="px-6 py-3.5">Gender</th>
                                        <th class="px-6 py-3.5">Date of Birth</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-if="section.student_profiles.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                            No students currently assigned to this section.
                                        </td>
                                    </tr>
                                    <tr v-for="(student, index) in section.student_profiles" :key="student.id" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4 text-xs text-muted-foreground">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 font-semibold text-foreground">
                                            <div class="flex items-center gap-3">
                                                <Avatar class="h-8 w-8 rounded-full border border-border">
                                                    <AvatarImage
                                                        v-if="student.user.avatar"
                                                        :src="student.user.avatar"
                                                        :alt="student.user.name"
                                                        class="object-cover"
                                                    />
                                                    <AvatarFallback class="bg-primary/10 text-primary font-bold text-xs">
                                                        {{ getInitials(student.user.name) }}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <span>{{ student.user.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs text-muted-foreground">{{ student.lrn }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                :class="student.gender === 'M' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300'"
                                            >
                                                {{ student.gender === 'M' ? 'Male' : 'Female' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-muted-foreground">{{ formatDate(student.birth_date) }}</td>
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
