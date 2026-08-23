<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useInitials } from '@/composables/useInitials';
import { UserPlus, Users, KeyRound, Search, Shield, GraduationCap, School, Upload, Camera, Loader2 } from '@lucide/vue';

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            role: string;
            credential: string;
            email: string | null;
            avatar?: string | null;
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

const { getInitials } = useInitials();

const search = ref(props.filters?.search || '');
const role = ref(props.filters?.role || '');
const resettingUserId = ref<number | null>(null);
const uploadingUserId = ref<number | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const selectedUserForPhoto = ref<{ id: number; name: string } | null>(null);

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
    if (confirm(`Are you sure you want to reset the password for "${user.name}"? It will be set back to their default birthday formula.`)) {
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

const triggerPhotoUpload = (user: { id: number; name: string }) => {
    selectedUserForPhoto.value = user;
    fileInputRef.value?.click();
};

const handlePhotoSelected = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    const user = selectedUserForPhoto.value;

    if (!file || !user) {
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        alert('Image file size must not exceed 2MB. Please choose a smaller photo.');
        target.value = '';
        selectedUserForPhoto.value = null;
        return;
    }

    uploadingUserId.value = user.id;
    const formData = new FormData();
    formData.append('photo', file);

    router.post(`/admin/users/${user.id}/photo`, formData, {
        preserveScroll: true,
        forceFormData: true,
        onFinish: () => {
            uploadingUserId.value = null;
            selectedUserForPhoto.value = null;
            if (fileInputRef.value) {
                fileInputRef.value.value = '';
            }
        },
    });
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
                <div class="flex flex-wrap items-center gap-3">
                    <Link href="/admin/users/students/import">
                        <Button variant="secondary" class="gap-2">
                            <Upload class="h-4 w-4" />
                            Bulk Import CSV
                        </Button>
                    </Link>
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

            <!-- Users Table -->
            <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-border bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3.5">Name</th>
                                <th class="px-4 py-3.5">School ID / Credential</th>
                                <th class="px-4 py-3.5">Role</th>
                                <th class="px-4 py-3.5">Email</th>
                                <th class="px-4 py-3.5">Details</th>
                                <th class="px-4 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-3.5 font-medium text-foreground">
                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            class="relative group rounded-full focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                            title="Click to upload/change photo"
                                            :disabled="uploadingUserId === user.id"
                                            @click="triggerPhotoUpload(user)"
                                        >
                                            <Avatar class="h-9 w-9 overflow-hidden rounded-full border border-border">
                                                <AvatarImage
                                                    v-if="user.avatar"
                                                    :src="user.avatar"
                                                    :alt="user.name"
                                                    class="h-full w-full object-cover"
                                                />
                                                <AvatarFallback class="bg-primary/10 text-primary font-bold text-xs">
                                                    {{ getInitials(user.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Camera class="h-3.5 w-3.5 text-white" />
                                            </div>
                                        </button>
                                        <div class="flex flex-col">
                                            <span>{{ user.name }}</span>
                                            <span v-if="user.email" class="text-xs text-muted-foreground sm:hidden">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-xs text-muted-foreground">
                                    {{ user.credential }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                            user.role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : '',
                                            user.role === 'teacher' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : '',
                                            user.role === 'student' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : '',
                                        ]"
                                    >
                                        {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-muted-foreground">
                                    {{ user.email || '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-muted-foreground">
                                    <span v-if="user.student_profile">
                                        Gender: {{ user.student_profile.gender }} | DOB: {{ user.student_profile.birth_date }}
                                    </span>
                                    <span v-else-if="user.teacher_profile">
                                        Emp #: {{ user.teacher_profile.employee_no }}
                                    </span>
                                    <span v-else>—</span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="h-8 text-xs gap-1.5 text-muted-foreground hover:text-foreground"
                                            :disabled="uploadingUserId === user.id"
                                            title="Upload ID Photo"
                                            @click="triggerPhotoUpload(user)"
                                        >
                                            <Loader2 v-if="uploadingUserId === user.id" class="h-3.5 w-3.5 animate-spin" />
                                            <Camera v-else class="h-3.5 w-3.5" />
                                            <span>{{ uploadingUserId === user.id ? 'Uploading...' : (user.avatar ? 'Change Photo' : 'Upload Photo') }}</span>
                                        </Button>

                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 text-xs gap-1.5 text-muted-foreground hover:text-foreground"
                                            :disabled="resettingUserId === user.id"
                                            @click="confirmResetPassword(user)"
                                        >
                                            <KeyRound class="h-3.5 w-3.5" />
                                            <span>{{ resettingUserId === user.id ? 'Resetting...' : 'Reset' }}</span>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-muted-foreground">
                                    No users found matching your search.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Hidden Photo File Input -->
                <input
                    ref="fileInputRef"
                    type="file"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="hidden"
                    @change="handlePhotoSelected"
                />

                <!-- Pagination -->
                <div
                    v-if="users.last_page > 1"
                    class="flex items-center justify-between border-t border-border px-4 py-3 text-xs text-muted-foreground"
                >
                    <div>
                        Showing page {{ users.current_page }} of {{ users.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in users.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-2.5 py-1 rounded border text-xs transition-colors',
                                link.active
                                    ? 'bg-primary text-primary-foreground border-primary font-medium'
                                    : link.url
                                    ? 'bg-background hover:bg-muted border-border text-foreground'
                                    : 'opacity-40 cursor-not-allowed border-transparent text-muted-foreground'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>
