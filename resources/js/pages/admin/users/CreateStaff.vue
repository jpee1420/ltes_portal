<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft, UserPlus } from '@lucide/vue';

const form = useForm({
    employee_no: '',
    role: 'teacher',
    first_name: '',
    middle_name: '',
    last_name: '',
    birth_date: '',
    email: '',
});

const submit = () => {
    form.post('/admin/users/staff', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <Head title="Create Staff Account — Admin" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-3xl mx-auto">
            <div class="flex items-center gap-4">
                <Link
                    href="/admin/users"
                    class="inline-flex items-center justify-center rounded-lg border border-border bg-background p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    aria-label="Back to Users"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Add New Staff Member</h1>
                    <p class="text-sm text-muted-foreground">Register a teacher or administrator account.</p>
                </div>
            </div>

            <Card class="rounded-xl border border-border/70 shadow-sm">
                <CardHeader class="pb-4">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <UserPlus class="h-5 w-5 text-primary" />
                        Staff Member Information
                    </CardTitle>
                    <CardDescription>
                        Default portal password will be generated as <span class="font-mono text-xs bg-muted px-1.5 py-0.5 rounded">MMDDYYYY + Last 4 of Employee Number</span> (or Employee Number if no birthday is set).
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="employee_no" class="text-sm font-medium">
                                    Employee Number <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="employee_no"
                                    v-model="form.employee_no"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="7"
                                    required
                                    autofocus
                                    placeholder="e.g. 2000001 (7 digits)"
                                    class="font-mono text-sm"
                                />
                                <InputError :message="form.errors.employee_no" />
                            </div>

                            <div class="space-y-2">
                                <Label for="role" class="text-sm font-medium">
                                    Account Role <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="role"
                                    v-model="form.role"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                >
                                    <option value="teacher">Teacher (Class Adviser)</option>
                                    <option value="admin">School Administrator</option>
                                </select>
                                <InputError :message="form.errors.role" />
                            </div>

                            <div class="space-y-2">
                                <Label for="first_name" class="text-sm font-medium">
                                    First Name <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    required
                                    placeholder="e.g. Maria"
                                />
                                <InputError :message="form.errors.first_name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="middle_name" class="text-sm font-medium">Middle Name</Label>
                                <Input
                                    id="middle_name"
                                    v-model="form.middle_name"
                                    type="text"
                                    placeholder="Optional"
                                />
                                <InputError :message="form.errors.middle_name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="last_name" class="text-sm font-medium">
                                    Last Name <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    required
                                    placeholder="e.g. Santos"
                                />
                                <InputError :message="form.errors.last_name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="birth_date" class="text-sm font-medium">Date of Birth (Optional)</Label>
                                <Input
                                    id="birth_date"
                                    v-model="form.birth_date"
                                    type="date"
                                    placeholder="YYYY-MM-DD"
                                />
                                <InputError :message="form.errors.birth_date" />
                            </div>

                            <div class="sm:col-span-2 space-y-2">
                                <Label for="email" class="text-sm font-medium">Email Address (Optional)</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="e.g. maria.santos@ltes.edu.ph"
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <Link
                                href="/admin/users"
                                class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                            >
                                Cancel
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2" />
                                <span>{{ form.processing ? 'Creating Account...' : 'Create Staff Account' }}</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
