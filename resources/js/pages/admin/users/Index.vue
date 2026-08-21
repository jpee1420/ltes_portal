<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { UserPlus, Users, KeyRound, Search, Shield, GraduationCap, School } from '@lucide/vue';

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            role: string;
            credential: string;
            email: string | null;
            created_at: string;
            student_profile?: { lrn: string; gender: string; birth_date: string };
            teacher_profile?: { employee_no: string };
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters?: {
        search?: string | null;
        role?: string | null;
    };
}>();

const search = ref(props.filters?.search || '');
const role = ref(props.filters?.role || '');
const resettingUserId = ref<number | null>(null);

let searchTimeout: ReturnType<typeof setTimeout>;
watch([search, role], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/admin/users',
            {
                search: search.value || undefined,
                role: role.value || undefined,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300);
});

const confirmResetPassword = (user: { id: number; name: string; credential: string }) => {
    if (confirm(`Are you sure you want to reset the password for "${user.name}"? It will be set back to their School ID (${user.credential}).`)) {
        resettingUserId.value = user.id;
        router.post(
            `/admin/users/${user.id}/reset-password`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    resettingUserId.value = null;
                },
            }
        );
    }
};
</script>

<template>
    <div>
        <Head title="User Accounts — Admin" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">User Management</h1>
                    <p class="text-sm text-muted-foreground">Manage student, teacher, and administrator accounts (Total: {{ users.total }}).</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link href="/admin/users/students/create">
                        <Button class="gap-2">
                            <GraduationCap class="h-4 w-4" />
                            Add Student
                        </Button>
                    </Link>
                    <Link href="/admin/users/staff/create">
                        <Button variant="outline" class="gap-2">
                            <School class="h-4 w-4" />
                            Add Staff / Teacher
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:w-80">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="search"
                        placeholder="Search by name or School ID..."
                        class="pl-9 h-10"
                    />
                </div>
                <div class="w-full sm:w-48">
                    <select
                        v-model="role"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    >
                        <option value="">All Roles</option>
                        <option value="student">Students</option>
                        <option value="teacher">Teachers</option>
                        <option value="admin">Administrators</option>
                    </select>
                </div>
            </div>

            <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                <CardHeader class="pb-3 border-b border-border bg-muted/20">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        Account Directory
                    </CardTitle>
                    <CardDescription>
                        User accounts with credential numbers and quick reset actions.
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-border text-xs uppercase text-muted-foreground bg-muted/40 font-semibold">
                                <tr>
                                    <th class="px-6 py-3.5">Name</th>
                                    <th class="px-6 py-3.5">Role</th>
                                    <th class="px-6 py-3.5">School ID / Credential</th>
                                    <th class="px-6 py-3.5">Email</th>
                                    <th class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-if="users.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                        <Users class="mx-auto h-8 w-8 mb-2 opacity-40" />
                                        No users found matching the filter criteria.
                                    </td>
                                </tr>
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-foreground">
                                        {{ user.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                            :class="{
                                                'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300': user.role === 'admin',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300': user.role === 'teacher',
                                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300': user.role === 'student',
                                            }"
                                        >
                                            <Shield v-if="user.role === 'admin'" class="h-3 w-3" />
                                            <School v-else-if="user.role === 'teacher'" class="h-3 w-3" />
                                            <GraduationCap v-else class="h-3 w-3" />
                                            {{ user.role === 'admin' ? 'Administrator' : user.role === 'teacher' ? 'Teacher' : 'Student' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs font-medium text-foreground">
                                        {{ user.credential }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-muted-foreground">
                                        {{ user.email || '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            @click="confirmResetPassword(user)"
                                            :disabled="resettingUserId === user.id"
                                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                            title="Reset Password to default credential"
                                        >
                                            <KeyRound class="h-3.5 w-3.5" />
                                            <span>{{ resettingUserId === user.id ? 'Resetting...' : 'Reset Password' }}</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.links.length > 3" class="flex items-center justify-between border-t border-border px-6 py-4">
                        <div class="text-xs text-muted-foreground">
                            Showing page {{ users.current_page }} of {{ users.last_page }}
                        </div>
                        <div class="flex items-center gap-1">
                            <Link
                                v-for="(link, i) in users.links"
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
