<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { FolderTree, Plus, Edit2, Users, School } from '@lucide/vue';

defineProps<{
    sections: {
        data: Array<{
            id: number;
            name: string;
            student_profiles_count: number;
            school_year: { id: number; label: string; is_active: boolean };
            grade_level: { id: number; name: string; order: number };
            adviser?: { id: number; name: string; credential: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
}>();
</script>

<template>
    <div>
        <Head title="Sections & Homerooms — Admin" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Section Management</h1>
                    <p class="text-sm text-muted-foreground">Manage classes, grade levels, and assigned teacher advisers.</p>
                </div>
                <div>
                    <Link href="/admin/sections/create">
                        <Button class="gap-2">
                            <Plus class="h-4 w-4" />
                            Create Section
                        </Button>
                    </Link>
                </div>
            </div>

            <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                <CardHeader class="pb-3 border-b border-border bg-muted/20">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <FolderTree class="h-5 w-5 text-primary" />
                        Class Sections Directory
                    </CardTitle>
                    <CardDescription>
                        All configured sections for the active and upcoming school years.
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                <tr>
                                    <th class="px-6 py-3.5">Section Name</th>
                                    <th class="px-6 py-3.5">Grade Level</th>
                                    <th class="px-6 py-3.5">School Year</th>
                                    <th class="px-6 py-3.5">Class Adviser</th>
                                    <th class="px-6 py-3.5 text-center">Enrolled Students</th>
                                    <th class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-if="sections.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                        <School class="mx-auto h-8 w-8 mb-2 opacity-40" />
                                        No sections configured yet. Click "Create Section" to get started.
                                    </td>
                                </tr>
                                <tr v-for="section in sections.data" :key="section.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-foreground">
                                        {{ section.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-md bg-secondary px-2.5 py-1 text-xs font-medium text-secondary-foreground">
                                            {{ section.grade_level.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-muted-foreground">
                                        <span :class="{'font-bold text-primary': section.school_year.is_active}">
                                            S.Y. {{ section.school_year.label }}
                                            <span v-if="section.school_year.is_active" class="ml-1 text-[10px] text-emerald-600 bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-400 px-1.5 py-0.5 rounded">Active</span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <div v-if="section.adviser" class="flex flex-col">
                                            <span class="font-medium text-foreground">{{ section.adviser.name }}</span>
                                            <span class="font-mono text-[10px] text-muted-foreground">ID: {{ section.adviser.credential }}</span>
                                        </div>
                                        <span v-else class="italic text-muted-foreground">Unassigned</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-muted-foreground">
                                            <Users class="h-3.5 w-3.5" />
                                            {{ section.student_profiles_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/sections/${section.id}`"
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/10 transition-colors"
                                        >
                                            <Users class="h-3.5 w-3.5" />
                                            Roster
                                        </Link>
                                        <Link
                                            :href="`/admin/sections/${section.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                        >
                                            <Edit2 class="h-3.5 w-3.5" />
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="sections.links.length > 3" class="flex items-center justify-between border-t border-border px-6 py-4">
                        <div class="text-xs text-muted-foreground">
                            Showing page {{ sections.current_page }} of {{ sections.last_page }}
                        </div>
                        <div class="flex items-center gap-1">
                            <Link
                                v-for="(link, i) in sections.links"
                                :key="i"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1.5 text-xs rounded-md font-medium transition-colors',
                                    link.active
                                        ? 'bg-primary text-primary-foreground font-semibold'
                                        : link.url
                                          ? 'hover:bg-muted text-foreground'
                                          : 'text-muted-foreground opacity-50 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
