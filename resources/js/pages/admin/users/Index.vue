<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import { formatDate } from '@/lib/utils';
import {
    Users,
    KeyRound,
    Search,
    GraduationCap,
    School,
    Upload,
    Camera,
    Loader2,
    Eye,
    Pencil,
    Shield,
    Check,
    AlertCircle,
    UserCheck,
} from '@lucide/vue';

interface StudentProfileData {
    id: number;
    lrn: string;
    first_name: string;
    last_name: string;
    middle_name?: string | null;
    gender: string;
    birth_date: string;
    section_id?: number | null;
    section?: {
        id: number;
        name: string;
        grade_level?: { id: number; name: string };
    } | null;
}

interface TeacherProfileData {
    id: number;
    employee_no: string;
    first_name: string;
    last_name: string;
    middle_name?: string | null;
    birth_date?: string | null;
}

interface UserItem {
    id: number;
    name: string;
    role: string;
    credential: string;
    email: string | null;
    avatar?: string | null;
    created_at: string;
    student_profile?: StudentProfileData | null;
    teacher_profile?: TeacherProfileData | null;
}

const props = defineProps<{
    users: {
        data: Array<UserItem>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    sections?: Array<{ id: number; name: string }>;
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

// Details & Edit Modal state
const isDetailsModalOpen = ref(false);
const isEditing = ref(false);
const selectedUser = ref<UserItem | null>(null);

const editForm = useForm({
    first_name: '',
    last_name: '',
    middle_name: '',
    name: '',
    email: '',
    gender: 'M',
    birth_date: '',
    section_id: null as number | null,
});

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

const openUserDetails = (user: UserItem) => {
    selectedUser.value = user;
    isEditing.value = false;
    editForm.clearErrors();
    populateEditForm(user);
    isDetailsModalOpen.value = true;
};

const populateEditForm = (user: UserItem) => {
    editForm.name = user.name || '';
    editForm.email = user.email || '';

    if (user.student_profile) {
        editForm.first_name = user.student_profile.first_name || '';
        editForm.last_name = user.student_profile.last_name || '';
        editForm.middle_name = user.student_profile.middle_name || '';
        editForm.gender = user.student_profile.gender || 'M';
        editForm.birth_date = user.student_profile.birth_date
            ? user.student_profile.birth_date.split('T')[0]
            : '';
        editForm.section_id = user.student_profile.section_id || null;
    } else if (user.teacher_profile) {
        editForm.first_name = user.teacher_profile.first_name || '';
        editForm.last_name = user.teacher_profile.last_name || '';
        editForm.middle_name = user.teacher_profile.middle_name || '';
        editForm.birth_date = user.teacher_profile.birth_date
            ? user.teacher_profile.birth_date.split('T')[0]
            : '';
    } else {
        editForm.first_name = '';
        editForm.last_name = '';
        editForm.middle_name = '';
        editForm.birth_date = '';
        editForm.section_id = null;
    }
};

const startEditing = () => {
    if (selectedUser.value) {
        populateEditForm(selectedUser.value);
    }
    isEditing.value = true;
};

const cancelEditing = () => {
    isEditing.value = false;
    editForm.clearErrors();
};

const saveUserChanges = () => {
    if (!selectedUser.value) return;

    editForm.put(`/admin/users/${selectedUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            // Update local selected user reference
            const updated = props.users.data.find((u) => u.id === selectedUser.value?.id);
            if (updated) {
                selectedUser.value = updated;
            }
        },
    });
};

const confirmResetPassword = (user: UserItem) => {
    if (
        confirm(
            `Are you sure you want to reset the password for "${user.name}"? It will be set back to their default birthday formula (MMDDYYYY + last 4 digits of credential).`
        )
    ) {
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
            if (selectedUser.value?.id === user.id) {
                const refreshed = props.users.data.find((u) => u.id === user.id);
                if (refreshed) {
                    selectedUser.value = refreshed;
                }
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
                    <p class="text-sm text-muted-foreground">
                        Manage student, teacher, and administrator accounts (Total: {{ users.total }}).
                    </p>
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
                                <td class="px-4 py-3.5">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 text-xs gap-1.5 font-medium hover:bg-primary/10 hover:text-primary hover:border-primary/40 transition-colors"
                                        @click="openUserDetails(user)"
                                    >
                                        <Eye class="h-3.5 w-3.5 text-muted-foreground" />
                                        <span>View Details</span>
                                    </Button>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 text-xs gap-1.5 text-muted-foreground hover:text-foreground"
                                            :disabled="uploadingUserId === user.id"
                                            title="Upload or change ID Photo"
                                            @click="triggerPhotoUpload(user)"
                                        >
                                            <Loader2 v-if="uploadingUserId === user.id" class="h-3.5 w-3.5 animate-spin" />
                                            <Camera v-else class="h-3.5 w-3.5" />
                                            <span>{{ uploadingUserId === user.id ? 'Uploading...' : (user.avatar ? 'Change Photo' : 'Upload Photo') }}</span>
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

            <!-- User Details & Edit Modal Dialog -->
            <Dialog :open="isDetailsModalOpen" @update:open="isDetailsModalOpen = $event">
                <DialogContent class="sm:max-w-[620px] max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-2 text-xl font-bold">
                            <UserCheck v-if="!isEditing" class="h-5 w-5 text-primary" />
                            <Pencil v-else class="h-5 w-5 text-primary" />
                            {{ isEditing ? 'Edit User Information' : 'User Account Details' }}
                        </DialogTitle>
                        <DialogDescription>
                            {{
                                isEditing
                                    ? `Update profile information for ${selectedUser?.name}.`
                                    : 'Comprehensive personal profile, institutional credential, and access settings.'
                            }}
                        </DialogDescription>
                    </DialogHeader>

                    <div v-if="selectedUser" class="space-y-5 py-2">
                        <!-- User Hero Header -->
                        <div class="flex items-center gap-4 rounded-xl border border-border bg-muted/30 p-4">
                            <div class="relative">
                                <Avatar class="h-16 w-16 overflow-hidden rounded-full border-2 border-background shadow-sm">
                                    <AvatarImage
                                        v-if="selectedUser.avatar"
                                        :src="selectedUser.avatar"
                                        :alt="selectedUser.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <AvatarFallback class="bg-primary/10 text-primary font-bold text-lg">
                                        {{ getInitials(selectedUser.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <button
                                    type="button"
                                    class="absolute -bottom-1 -right-1 rounded-full bg-primary text-primary-foreground p-1 shadow-sm hover:bg-primary/90 transition-colors"
                                    title="Upload / Change Photo"
                                    @click="triggerPhotoUpload(selectedUser)"
                                >
                                    <Camera class="h-3 w-3" />
                                </button>
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-foreground">{{ selectedUser.name }}</h3>
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
                                            selectedUser.role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : '',
                                            selectedUser.role === 'teacher' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : '',
                                            selectedUser.role === 'student' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : '',
                                        ]"
                                    >
                                        {{ selectedUser.role.charAt(0).toUpperCase() + selectedUser.role.slice(1) }}
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-muted-foreground">
                                    {{ selectedUser.role === 'student' ? '12-Digit LRN' : 'Employee Number' }}:
                                    <span class="font-semibold text-foreground">{{ selectedUser.credential }}</span>
                                </p>
                                <p v-if="selectedUser.email" class="text-xs text-muted-foreground">
                                    {{ selectedUser.email }}
                                </p>
                            </div>
                        </div>

                        <!-- View Mode Details -->
                        <div v-if="!isEditing" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div class="rounded-lg border border-border/70 p-3 bg-card">
                                    <span class="text-xs font-medium text-muted-foreground">Full Name</span>
                                    <p class="text-sm font-semibold text-foreground mt-0.5">{{ selectedUser.name }}</p>
                                </div>

                                <div class="rounded-lg border border-border/70 p-3 bg-card">
                                    <span class="text-xs font-medium text-muted-foreground">Email Address</span>
                                    <p class="text-sm font-semibold text-foreground mt-0.5">{{ selectedUser.email || 'None provided' }}</p>
                                </div>

                                <!-- Student specific details -->
                                <template v-if="selectedUser.student_profile">
                                    <div class="rounded-lg border border-border/70 p-3 bg-card">
                                        <span class="text-xs font-medium text-muted-foreground">Gender</span>
                                        <p class="text-sm font-semibold text-foreground mt-0.5">
                                            {{ selectedUser.student_profile.gender === 'M' ? 'Male' : 'Female' }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg border border-border/70 p-3 bg-card">
                                        <span class="text-xs font-medium text-muted-foreground">Date of Birth</span>
                                        <p class="text-sm font-semibold text-foreground mt-0.5">
                                            {{ formatDate(selectedUser.student_profile.birth_date) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg border border-border/70 p-3 bg-card sm:col-span-2">
                                        <span class="text-xs font-medium text-muted-foreground">Assigned Homeroom Section</span>
                                        <p class="text-sm font-semibold text-foreground mt-0.5">
                                            {{
                                                selectedUser.student_profile.section
                                                    ? `${selectedUser.student_profile.section.grade_level?.name || ''} - ${selectedUser.student_profile.section.name}`
                                                    : 'Unassigned (Not enrolled in any section)'
                                            }}
                                        </p>
                                    </div>
                                </template>

                                <!-- Teacher specific details -->
                                <template v-else-if="selectedUser.teacher_profile">
                                    <div class="rounded-lg border border-border/70 p-3 bg-card">
                                        <span class="text-xs font-medium text-muted-foreground">Date of Birth</span>
                                        <p class="text-sm font-semibold text-foreground mt-0.5">
                                            {{ formatDate(selectedUser.teacher_profile.birth_date) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg border border-border/70 p-3 bg-card">
                                        <span class="text-xs font-medium text-muted-foreground">Employee Number</span>
                                        <p class="text-sm font-mono font-semibold text-foreground mt-0.5">
                                            {{ selectedUser.teacher_profile.employee_no }}
                                        </p>
                                    </div>
                                </template>

                                <div class="rounded-lg border border-border/70 p-3 bg-card sm:col-span-2">
                                    <span class="text-xs font-medium text-muted-foreground">Account Created</span>
                                    <p class="text-sm font-semibold text-foreground mt-0.5">
                                        {{ formatDate(selectedUser.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Isolated Safe Danger Zone for Password Reset -->
                            <div class="rounded-xl border border-amber-200 bg-amber-50/60 dark:border-amber-900/40 dark:bg-amber-950/20 p-4 space-y-3">
                                <div class="flex items-start gap-3">
                                    <div class="rounded-full bg-amber-500/10 p-2 text-amber-600 dark:text-amber-400 shrink-0">
                                        <KeyRound class="h-4 w-4" />
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-foreground">Password Administration</h4>
                                        <p class="text-xs text-muted-foreground mt-0.5">
                                            Reset this user's password back to the default institutional formula (Date of Birth <code class="text-xs font-mono">MMDDYYYY</code> + last 4 digits of credential).
                                        </p>
                                    </div>
                                </div>
                                <div class="flex justify-end pt-1">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        class="h-8 text-xs border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40 gap-1.5"
                                        :disabled="resettingUserId === selectedUser.id"
                                        @click="confirmResetPassword(selectedUser)"
                                    >
                                        <Loader2 v-if="resettingUserId === selectedUser.id" class="h-3.5 w-3.5 animate-spin" />
                                        <KeyRound v-else class="h-3.5 w-3.5" />
                                        <span>{{ resettingUserId === selectedUser.id ? 'Resetting Password...' : 'Reset to Default Password' }}</span>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Mode Form -->
                        <div v-else class="space-y-4">
                            <!-- Name Inputs -->
                            <template v-if="selectedUser.student_profile || selectedUser.teacher_profile">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="space-y-1.5">
                                        <Label for="edit-first-name" class="text-xs font-medium">First Name *</Label>
                                        <Input
                                            id="edit-first-name"
                                            v-model="editForm.first_name"
                                            type="text"
                                            required
                                            class="h-9 text-sm"
                                        />
                                        <p v-if="editForm.errors.first_name" class="text-xs text-destructive">
                                            {{ editForm.errors.first_name }}
                                        </p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label for="edit-middle-name" class="text-xs font-medium">Middle Name</Label>
                                        <Input
                                            id="edit-middle-name"
                                            v-model="editForm.middle_name"
                                            type="text"
                                            class="h-9 text-sm"
                                        />
                                        <p v-if="editForm.errors.middle_name" class="text-xs text-destructive">
                                            {{ editForm.errors.middle_name }}
                                        </p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label for="edit-last-name" class="text-xs font-medium">Last Name *</Label>
                                        <Input
                                            id="edit-last-name"
                                            v-model="editForm.last_name"
                                            type="text"
                                            required
                                            class="h-9 text-sm"
                                        />
                                        <p v-if="editForm.errors.last_name" class="text-xs text-destructive">
                                            {{ editForm.errors.last_name }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="space-y-1.5">
                                    <Label for="edit-name" class="text-xs font-medium">Full Name *</Label>
                                    <Input
                                        id="edit-name"
                                        v-model="editForm.name"
                                        type="text"
                                        required
                                        class="h-9 text-sm"
                                    />
                                    <p v-if="editForm.errors.name" class="text-xs text-destructive">
                                        {{ editForm.errors.name }}
                                    </p>
                                </div>
                            </template>

                            <!-- Email Input -->
                            <div class="space-y-1.5">
                                <Label for="edit-email" class="text-xs font-medium">Email Address</Label>
                                <Input
                                    id="edit-email"
                                    v-model="editForm.email"
                                    type="email"
                                    placeholder="user@example.com"
                                    class="h-9 text-sm"
                                />
                                <p v-if="editForm.errors.email" class="text-xs text-destructive">
                                    {{ editForm.errors.email }}
                                </p>
                            </div>

                            <!-- Student Fields -->
                            <template v-if="selectedUser.student_profile">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <Label for="edit-gender" class="text-xs font-medium">Gender</Label>
                                        <select
                                            id="edit-gender"
                                            v-model="editForm.gender"
                                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        >
                                            <option value="M">Male</option>
                                            <option value="F">Female</option>
                                        </select>
                                        <p v-if="editForm.errors.gender" class="text-xs text-destructive">
                                            {{ editForm.errors.gender }}
                                        </p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label for="edit-birth-date" class="text-xs font-medium">Date of Birth</Label>
                                        <Input
                                            id="edit-birth-date"
                                            v-model="editForm.birth_date"
                                            type="date"
                                            class="h-9 text-sm"
                                        />
                                        <p v-if="editForm.errors.birth_date" class="text-xs text-destructive">
                                            {{ editForm.errors.birth_date }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="sections && sections.length > 0" class="space-y-1.5">
                                    <Label for="edit-section" class="text-xs font-medium">Assigned Section</Label>
                                    <select
                                        id="edit-section"
                                        v-model="editForm.section_id"
                                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
                                        <option :value="null">-- Unassigned --</option>
                                        <option v-for="sec in sections" :key="sec.id" :value="sec.id">
                                            {{ sec.name }}
                                        </option>
                                    </select>
                                    <p v-if="editForm.errors.section_id" class="text-xs text-destructive">
                                        {{ editForm.errors.section_id }}
                                    </p>
                                </div>
                            </template>

                            <!-- Teacher Fields -->
                            <template v-else-if="selectedUser.teacher_profile">
                                <div class="space-y-1.5">
                                    <Label for="edit-teacher-dob" class="text-xs font-medium">Date of Birth</Label>
                                    <Input
                                        id="edit-teacher-dob"
                                        v-model="editForm.birth_date"
                                        type="date"
                                        class="h-9 text-sm"
                                    />
                                    <p v-if="editForm.errors.birth_date" class="text-xs text-destructive">
                                        {{ editForm.errors.birth_date }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <DialogFooter class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-3 border-t border-border">
                        <template v-if="!isEditing">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="isDetailsModalOpen = false"
                            >
                                Close
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                class="gap-1.5"
                                @click="startEditing"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                                <span>Edit Information</span>
                            </Button>
                        </template>
                        <template v-else>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :disabled="editForm.processing"
                                @click="cancelEditing"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                class="gap-1.5"
                                :disabled="editForm.processing"
                                @click="saveUserChanges"
                            >
                                <Loader2 v-if="editForm.processing" class="h-3.5 w-3.5 animate-spin" />
                                <Check v-else class="h-3.5 w-3.5" />
                                <span>Save Changes</span>
                            </Button>
                        </template>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
