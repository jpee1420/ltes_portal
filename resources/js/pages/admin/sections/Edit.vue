<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { ArrowLeft, Edit2 } from '@lucide/vue';

const props = defineProps<{
    section: {
        id: number;
        name: string;
        school_year_id: number;
        grade_level_id: number;
        adviser_id: number | null;
        school_year: { label: string; is_active: boolean };
        grade_level: { name: string };
        adviser?: { name: string } | null;
    };
    teachers: Array<{ id: number; name: string; credential: string }>;
}>();

const form = useForm({
    name: props.section.name,
    adviser_id: props.section.adviser_id ?? ('' as number | string | null),
});

const submit = () => {
    form.put(`/admin/sections/${props.section.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <Head :title="`Edit Section ${section.name} — Admin`" />

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
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Edit Section</h1>
                    <p class="text-sm text-muted-foreground">{{ section.grade_level.name }} • S.Y. {{ section.school_year.label }}</p>
                </div>
            </div>

            <Card class="rounded-xl border border-border/70 shadow-sm">
                <CardHeader class="pb-4">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <Edit2 class="h-5 w-5 text-primary" />
                        Modify Section Details
                    </CardTitle>
                    <CardDescription>
                        Update section name or change assigned homeroom adviser.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-muted-foreground">Grade Level & School Year</Label>
                                <div class="rounded-md border border-input bg-muted/40 px-3 py-2 text-sm text-foreground">
                                    {{ section.grade_level.name }} &mdash; S.Y. {{ section.school_year.label }}
                                </div>
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
                                    <option :value="''">-- Unassigned --</option>
                                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.name }} (ID: {{ teacher.credential }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.adviser_id" />
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
                                <span>{{ form.processing ? 'Saving...' : 'Save Changes' }}</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
