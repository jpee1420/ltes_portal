<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft, Plus } from '@lucide/vue';

const props = defineProps<{
    schoolYears: Array<{ id: number; label: string; is_active: boolean }>;
    gradeLevels: Array<{ id: number; name: string; order: number }>;
    teachers: Array<{ id: number; name: string; credential: string }>;
}>();

const activeSy = props.schoolYears.find(sy => sy.is_active) || props.schoolYears[0];

const form = useForm({
    school_year_id: activeSy?.id || '',
    grade_level_id: props.gradeLevels[0]?.id || '',
    name: '',
    adviser_id: '' as number | string | null,
});

const submit = () => {
    form.post('/admin/sections', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <Head title="Create Section — Admin" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8 max-w-2xl mx-auto">
            <div class="flex items-center gap-4">
                <Link
                    href="/admin/sections"
                    class="inline-flex items-center justify-center rounded-lg border border-border bg-background p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    aria-label="Back to Sections"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Create New Section</h1>
                    <p class="text-sm text-muted-foreground">Define a classroom section and assign its homeroom adviser.</p>
                </div>
            </div>

            <Card class="rounded-xl border border-border/70 shadow-sm">
                <CardHeader class="pb-4">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <Plus class="h-5 w-5 text-primary" />
                        Section Details
                    </CardTitle>
                    <CardDescription>
                        Sections are organized by School Year and Grade Level.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label for="school_year_id" class="text-sm font-medium">
                                    School Year <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="school_year_id"
                                    v-model="form.school_year_id"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                >
                                    <option v-for="sy in schoolYears" :key="sy.id" :value="sy.id">
                                        S.Y. {{ sy.label }} {{ sy.is_active ? '(Active)' : '' }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.school_year_id" />
                            </div>

                            <div class="space-y-2">
                                <Label for="grade_level_id" class="text-sm font-medium">
                                    Grade Level <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="grade_level_id"
                                    v-model="form.grade_level_id"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                >
                                    <option v-for="gl in gradeLevels" :key="gl.id" :value="gl.id">
                                        {{ gl.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.grade_level_id" />
                            </div>

                            <div class="space-y-2">
                                <Label for="name" class="text-sm font-medium">
                                    Section Name <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autofocus
                                    placeholder="e.g. Mabini, Rizal, Sampaguita"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="adviser_id" class="text-sm font-medium">Class Adviser (Homeroom Teacher)</Label>
                                <select
                                    id="adviser_id"
                                    v-model="form.adviser_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                >
                                    <option :value="null">-- Unassigned --</option>
                                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.name }} (ID: {{ teacher.credential }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.adviser_id" />
                                <p class="text-xs text-muted-foreground">The assigned adviser can encode quarterly grades and daily attendance for students in this section.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <Link
                                href="/admin/sections"
                                class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-muted"
                            >
                                Cancel
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" class="mr-2" />
                                <span>{{ form.processing ? 'Creating...' : 'Create Section' }}</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
