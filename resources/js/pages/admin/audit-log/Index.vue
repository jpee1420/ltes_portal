<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { ClipboardList, Search, Activity, Shield, User, Globe, Clock } from '@lucide/vue';

const props = defineProps<{
    logs: {
        data: Array<{
            id: number;
            action: string;
            description: string | null;
            ip_address: string | null;
            user_agent: string | null;
            created_at: string;
            user: {
                id: number;
                name: string;
                credential: string;
                role: string;
            } | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(search, (newVal) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/admin/audit-log',
            { search: newVal || undefined },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const getActionVariant = (action: string): 'default' | 'secondary' | 'outline' | 'destructive' => {
    if (action.includes('failed') || action.includes('deleted')) return 'destructive';
    if (action.includes('login') || action.includes('created')) return 'default';
    if (action.includes('updated') || action.includes('recorded') || action.includes('saved')) return 'secondary';
    return 'outline';
};

const formatDate = (dateStr: string) => {
    try {
        const d = new Date(dateStr);
        return new Intl.DateTimeFormat('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        }).format(d);
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <div>
        <Head title="Audit Log & Activity Trail — Admin" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2.5">
                        <ClipboardList class="h-6 w-6 text-primary" />
                        Audit Log & Activity Trail
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Institutional audit trail capturing logins, system events, grade submissions, and account mutations.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-card text-xs font-medium text-muted-foreground">
                        <Activity class="h-3.5 w-3.5 text-primary" />
                        <span>Total Records: {{ logs.total.toLocaleString() }}</span>
                    </span>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search by action, description, user, or IP..."
                        class="pl-9"
                    />
                </div>
            </div>

            <!-- Logs Table Card -->
            <Card class="rounded-xl border border-border/70 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-border bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3.5">Timestamp</th>
                                <th class="px-4 py-3.5">User</th>
                                <th class="px-4 py-3.5">Action</th>
                                <th class="px-4 py-3.5">Description</th>
                                <th class="px-4 py-3.5">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-muted-foreground">
                                    <div class="flex items-center gap-1.5 font-mono">
                                        <Clock class="h-3.5 w-3.5 shrink-0 opacity-70" />
                                        <span>{{ formatDate(log.created_at) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div v-if="log.user" class="flex items-center gap-2">
                                        <div class="h-7 w-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold uppercase">
                                            {{ log.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-foreground text-xs">{{ log.user.name }}</div>
                                            <div class="text-[11px] font-mono text-muted-foreground">{{ log.user.credential }}</div>
                                        </div>
                                    </div>
                                    <div v-else class="text-xs text-muted-foreground italic flex items-center gap-1">
                                        <Shield class="h-3.5 w-3.5 opacity-60" />
                                        <span>System / Guest</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <Badge :variant="getActionVariant(log.action)" class="font-mono text-[11px] px-2 py-0.5">
                                        {{ log.action }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-xs text-foreground max-w-md break-words">
                                    {{ log.description || '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-muted-foreground">
                                    <div class="flex items-center gap-1">
                                        <Globe class="h-3 w-3 opacity-60" />
                                        <span>{{ log.ip_address || '—' }}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                    <ClipboardList class="h-8 w-8 mx-auto mb-2 text-muted-foreground/50" />
                                    No audit logs found matching your criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="logs.last_page > 1"
                    class="flex items-center justify-between border-t border-border px-4 py-3 text-xs text-muted-foreground"
                >
                    <div>
                        Showing page {{ logs.current_page }} of {{ logs.last_page }}
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in logs.links"
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
