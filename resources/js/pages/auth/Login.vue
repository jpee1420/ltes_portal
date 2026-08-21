<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

defineOptions({
    layout: {
        title: 'Lipit-Tomeeng Elementary School',
        description: 'Sign in to access your student, teacher, or administrative portal',
    },
});

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();
</script>

<template>
    <Head title="Sign In — Lipit-Tomeeng Elementary School" />

    <div
        v-if="status"
        class="mb-4 rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-700 dark:bg-green-950/50 dark:text-green-300"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="credential" class="text-sm font-medium">School ID</Label>
                <Input
                    id="credential"
                    type="text"
                    name="credential"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="username"
                    inputmode="numeric"
                    placeholder="12-digit LRN or 7-digit Employee No."
                    class="h-10 text-base md:text-sm"
                />
                <InputError :message="errors.credential" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-sm font-medium">Password</Label>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="h-10 text-base md:text-sm"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex cursor-pointer items-center space-x-2.5 text-sm text-muted-foreground hover:text-foreground">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Keep me signed in</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 h-10 w-full font-medium"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" class="mr-2" />
                <span>{{ processing ? 'Signing in...' : 'Sign In' }}</span>
            </Button>
        </div>

        <div class="rounded-lg border border-border/60 bg-muted/40 p-3 text-center text-xs text-muted-foreground">
            <p><strong>Note for Parents & Students:</strong> Your default password is your 12-digit Learner Reference Number (LRN).</p>
        </div>
    </Form>
</template>
