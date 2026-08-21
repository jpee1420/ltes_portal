<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft, CalendarPlus } from '@lucide/vue';

const form = useForm({
    label: '',
    start_date: '',
    end_date: '',
    is_active: false,
});

const submit = () => {
    form.post('/admin/school-years', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <Head title="Create School Year — Admin" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-2xl mx-auto">
            <div class="flex items-center gap-4">
                <Link
                    href="/admin/school-years"
                    class="inline-flex items-center justify-center rounded-lg border border-border bg-background p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    aria-label="Back to School Years"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Create School Year</h1>
                    <p class="text-sm text-muted-foreground">Define a new academic calendar cycle.</p>
                </div>
            </div>

            <Card class="rounded-xl border border-border/70 shadow-sm">
                <CardHeader class="pb-4">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <CalendarPlus class="h-5 w-5 text-primary" />
                        Academic Year Info
                    </CardTitle>
                    <CardDescription>
                        Set the label and date boundaries for this academic cycle.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label for="label" class="text-sm font-medium">
                                    School Year Label <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="label"
                                    v-model="form.label"
                                    type="text"
                                    required
                                    autofocus
                                    placeholder="e.g. 2027-2028"
                                />
                                <InputError :message="form.errors.label" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="start_date" class="text-sm font-medium">
                                        Opening / Start Date <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="start_date"
                                        v-model="form.start_date"
                                        type="date"
                                        required
                                    />
                                    <InputError :message="form.errors.start_date" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="end_date" class="text-sm font-medium">
                                        Closing / End Date <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="end_date"
                                        v-model="form.end_date"
                                        type="date"
                                        required
                                    />
                                    <InputError :message="form.errors.end_date" />
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <Label for="is_active" class="text-sm font-medium cursor-pointer">
                                    Set as active school year immediately
                                </Label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <Link
                                href="/admin/school-years"
                                class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                            >
                                Cancel
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2" />
                                <span>{{ form.processing ? 'Creating...' : 'Create School Year' }}</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
