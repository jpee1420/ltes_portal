<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Plus,
    Calendar as CalendarIcon,
    List,
    Clock,
    Tag,
    Layers,
    Pencil,
    Trash2,
    CheckCircle2,
    AlertCircle,
    Info,
} from '@lucide/vue';

interface CalendarEventItem {
    id: number;
    title: string;
    description: string | null;
    category: string;
    category_label: string;
    color: string;
    start_date: string;
    end_date: string | null;
    is_school_wide: boolean;
    section_id: number | null;
    section_name: string | null;
    created_by: number;
    creator_name: string | null;
    can_edit: boolean;
}

interface CategoryOption {
    value: string;
    label: string;
    color: string;
    is_school_wide: boolean;
}

interface SectionOption {
    id: number;
    name: string;
}

const props = defineProps<{
    year: number;
    month: number;
    schoolYear: { id: number; label: string } | null;
    events: CalendarEventItem[];
    upcomingEvents: CalendarEventItem[];
    sections: SectionOption[];
    categories: CategoryOption[];
}>();

// Active View State: 'grid' | 'agenda'
const activeView = ref<'grid' | 'agenda'>('grid');
const selectedCategory = ref<string>('all');

// Modals
const isCreateOpen = ref(false);
const isDetailOpen = ref(false);
const isEditOpen = ref(false);
const isDeleteConfirmOpen = ref(false);
const selectedEvent = ref<CalendarEventItem | null>(null);

// Forms
const createForm = useForm({
    title: '',
    description: '',
    category: 'school_event',
    start_date: '',
    end_date: '',
    section_id: null as number | null,
});

const editForm = useForm({
    title: '',
    description: '',
    category: 'school_event',
    start_date: '',
    end_date: '',
    section_id: null as number | null,
});

// Month Name
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const monthTitle = computed(() => `${monthNames[props.month - 1]} ${props.year}`);

// Navigation
function navigateMonth(step: number) {
    let newMonth = props.month + step;
    let newYear = props.year;

    if (newMonth < 1) {
        newMonth = 12;
        newYear -= 1;
    } else if (newMonth > 12) {
        newMonth = 1;
        newYear += 1;
    }

    router.get('/admin/calendar', { year: newYear, month: newMonth }, { preserveState: true });
}

function goToToday() {
    const today = new Date();
    router.get('/admin/calendar', { year: today.getFullYear(), month: today.getMonth() + 1 }, { preserveState: true });
}

// Category filter
const filteredEvents = computed(() => {
    if (selectedCategory.value === 'all') {
        return props.events;
    }
    return props.events.filter(e => e.category === selectedCategory.value);
});

// Build Calendar Grid (6 weeks × 7 days)
interface CalendarDayCell {
    dateString: string;
    dayNumber: number;
    isCurrentMonth: boolean;
    isToday: boolean;
    events: CalendarEventItem[];
}

const calendarGrid = computed<CalendarDayCell[]>(() => {
    const cells: CalendarDayCell[] = [];
    const todayStr = new Date().toISOString().split('T')[0];

    // First day of target month
    const firstDay = new Date(props.year, props.month - 1, 1);
    const startingDayOfWeek = firstDay.getDay(); // 0 = Sunday

    // Total days in month
    const daysInMonth = new Date(props.year, props.month, 0).getDate();

    // Days in previous month
    const daysInPrevMonth = new Date(props.year, props.month - 1, 0).getDate();

    // 1. Previous month filler days
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        const prevMonth = props.month === 1 ? 12 : props.month - 1;
        const prevYear = props.month === 1 ? props.year - 1 : props.year;
        const dateString = `${prevYear}-${String(prevMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        cells.push({
            dateString,
            dayNumber: day,
            isCurrentMonth: false,
            isToday: dateString === todayStr,
            events: getEventsForDate(dateString),
        });
    }

    // 2. Current month days
    for (let day = 1; day <= daysInMonth; day++) {
        const dateString = `${props.year}-${String(props.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        cells.push({
            dateString,
            dayNumber: day,
            isCurrentMonth: true,
            isToday: dateString === todayStr,
            events: getEventsForDate(dateString),
        });
    }

    // 3. Next month filler days (to fill 35 or 42 cells)
    const remainingCells = (cells.length > 35 ? 42 : 35) - cells.length;
    for (let day = 1; day <= remainingCells; day++) {
        const nextMonth = props.month === 12 ? 1 : props.month + 1;
        const nextYear = props.month === 12 ? props.year + 1 : props.year;
        const dateString = `${nextYear}-${String(nextMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        cells.push({
            dateString,
            dayNumber: day,
            isCurrentMonth: false,
            isToday: dateString === todayStr,
            events: getEventsForDate(dateString),
        });
    }

    return cells;
});

function getEventsForDate(dateStr: string): CalendarEventItem[] {
    return filteredEvents.value.filter(e => {
        if (!e.end_date) {
            return e.start_date === dateStr;
        }
        return e.start_date <= dateStr && e.end_date >= dateStr;
    });
}

function getCategoryBadgeClass(color: string) {
    switch (color) {
        case 'red':
            return 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/20 hover:bg-rose-500/20';
        case 'amber':
            return 'bg-amber-500/10 text-amber-800 dark:text-amber-300 border-amber-500/20 hover:bg-amber-500/20';
        case 'blue':
            return 'bg-blue-500/10 text-blue-800 dark:text-blue-300 border-blue-500/20 hover:bg-blue-500/20';
        case 'purple':
            return 'bg-purple-500/10 text-purple-800 dark:text-purple-300 border-purple-500/20 hover:bg-purple-500/20';
        case 'emerald':
            return 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 border-emerald-500/20 hover:bg-emerald-500/20';
        default:
            return 'bg-primary/10 text-primary border-primary/20';
    }
}

function getCategoryDotClass(color: string) {
    switch (color) {
        case 'red': return 'bg-rose-500';
        case 'amber': return 'bg-amber-500';
        case 'blue': return 'bg-blue-500';
        case 'purple': return 'bg-purple-500';
        case 'emerald': return 'bg-emerald-500';
        default: return 'bg-primary';
    }
}

function openCreateModal(dateStr?: string) {
    createForm.reset();
    createForm.category = 'school_event';
    if (dateStr) {
        createForm.start_date = dateStr;
    }
    isCreateOpen.value = true;
}

function submitCreate() {
    createForm.post('/admin/calendar', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateOpen.value = false;
            createForm.reset();
        },
    });
}

function viewEventDetails(event: CalendarEventItem) {
    selectedEvent.value = event;
    isDetailOpen.value = true;
}

function openEditModal(event: CalendarEventItem) {
    selectedEvent.value = event;
    editForm.title = event.title;
    editForm.description = event.description || '';
    editForm.category = event.category;
    editForm.start_date = event.start_date;
    editForm.end_date = event.end_date || '';
    editForm.section_id = event.section_id;

    isDetailOpen.value = false;
    isEditOpen.value = true;
}

function submitEdit() {
    if (!selectedEvent.value) return;

    editForm.put(`/admin/calendar/${selectedEvent.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditOpen.value = false;
            selectedEvent.value = null;
        },
    });
}

function confirmDelete(event: CalendarEventItem) {
    selectedEvent.value = event;
    isDetailOpen.value = false;
    isDeleteConfirmOpen.value = true;
}

function submitDelete() {
    if (!selectedEvent.value) return;

    router.delete(`/admin/calendar/${selectedEvent.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteConfirmOpen.value = false;
            selectedEvent.value = null;
        },
    });
}
</script>

<template>
    <div>
        <Head title="School Calendar — Lipit-Tomeeng Elementary School" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-8">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                            <CalendarDays class="h-3.5 w-3.5" />
                            Academic Calendar
                        </span>
                        <span v-if="schoolYear" class="text-xs text-muted-foreground">
                            &bull; S.Y. <strong class="text-foreground font-medium">{{ schoolYear.label }}</strong>
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground mt-1">
                        School Calendar &amp; Activities
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Schedule and manage DepEd activities, examinations, holidays, and school-wide events.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- View switcher -->
                    <div class="inline-flex rounded-lg border border-border bg-muted/40 p-0.5">
                        <button
                            type="button"
                            @click="activeView = 'grid'"
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all',
                                activeView === 'grid' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                            ]"
                        >
                            <CalendarIcon class="h-3.5 w-3.5" />
                            Month Grid
                        </button>
                        <button
                            type="button"
                            @click="activeView = 'agenda'"
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all',
                                activeView === 'agenda' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                            ]"
                        >
                            <List class="h-3.5 w-3.5" />
                            Agenda
                        </button>
                    </div>

                    <Button @click="openCreateModal()" class="gap-1.5 shadow-sm">
                        <Plus class="h-4 w-4" />
                        Add Event
                    </Button>
                </div>
            </div>

            <!-- Month Navigator & Filter Bar -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-xl border border-border bg-card p-4 shadow-sm">
                <!-- Navigation -->
                <div class="flex items-center gap-3">
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight text-foreground min-w-[180px]">
                        {{ monthTitle }}
                    </h2>
                    <div class="flex items-center gap-1">
                        <Button variant="outline" size="icon" class="h-8 w-8" @click="navigateMonth(-1)">
                            <ChevronLeft class="h-4 w-4" />
                            <span class="sr-only">Previous month</span>
                        </Button>
                        <Button variant="outline" size="sm" class="h-8 px-2.5 text-xs font-medium" @click="goToToday()">
                            Today
                        </Button>
                        <Button variant="outline" size="icon" class="h-8 w-8" @click="navigateMonth(1)">
                            <ChevronRight class="h-4 w-4" />
                            <span class="sr-only">Next month</span>
                        </Button>
                    </div>
                </div>

                <!-- Category filter chips -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <button
                        type="button"
                        @click="selectedCategory = 'all'"
                        :class="[
                            'rounded-full px-2.5 py-1 text-xs font-medium transition-colors border',
                            selectedCategory === 'all'
                                ? 'bg-primary text-primary-foreground border-primary'
                                : 'bg-muted/40 text-muted-foreground border-border hover:bg-muted'
                        ]"
                    >
                        All ({{ events.length }})
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.value"
                        type="button"
                        @click="selectedCategory = cat.value"
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition-colors border',
                            selectedCategory === cat.value
                                ? 'bg-foreground text-background border-foreground font-semibold'
                                : 'bg-muted/40 text-muted-foreground border-border hover:bg-muted'
                        ]"
                    >
                        <span class="h-2 w-2 rounded-full" :class="getCategoryDotClass(cat.color)" />
                        {{ cat.label }}
                    </button>
                </div>
            </div>

            <!-- VIEW 1: MONTHLY CALENDAR GRID -->
            <div v-if="activeView === 'grid'" class="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
                <!-- Day of week headers -->
                <div class="grid grid-cols-7 border-b border-border bg-muted/40 text-center text-xs font-bold text-muted-foreground">
                    <div class="py-2.5">Sun</div>
                    <div class="py-2.5">Mon</div>
                    <div class="py-2.5">Tue</div>
                    <div class="py-2.5">Wed</div>
                    <div class="py-2.5">Thu</div>
                    <div class="py-2.5">Fri</div>
                    <div class="py-2.5">Sat</div>
                </div>

                <!-- Calendar day cells -->
                <div class="grid grid-cols-7 divide-x divide-y divide-border">
                    <div
                        v-for="(cell, index) in calendarGrid"
                        :key="index"
                        :class="[
                            'min-h-[110px] sm:min-h-[130px] p-1.5 sm:p-2 transition-colors flex flex-col justify-between group',
                            !cell.isCurrentMonth ? 'bg-muted/20 text-muted-foreground' : 'bg-card text-foreground',
                            cell.isToday ? 'bg-primary/5' : ''
                        ]"
                    >
                        <!-- Top header of day cell -->
                        <div class="flex items-center justify-between">
                            <span
                                :class="[
                                    'inline-flex items-center justify-center text-xs font-bold rounded-full h-6 w-6',
                                    cell.isToday
                                        ? 'bg-primary text-primary-foreground font-black shadow-sm'
                                        : cell.isCurrentMonth
                                            ? 'text-foreground'
                                            : 'text-muted-foreground'
                                ]"
                            >
                                {{ cell.dayNumber }}
                            </span>

                            <!-- Quick add button on hover -->
                            <button
                                type="button"
                                @click="openCreateModal(cell.dateString)"
                                class="opacity-0 group-hover:opacity-100 rounded p-1 text-muted-foreground hover:text-primary hover:bg-muted transition-all"
                                title="Add event on this date"
                            >
                                <Plus class="h-3.5 w-3.5" />
                            </button>
                        </div>

                        <!-- Event Pills List -->
                        <div class="mt-1 flex-1 space-y-1 overflow-y-auto max-h-[85px] scrollbar-thin">
                            <button
                                v-for="event in cell.events.slice(0, 3)"
                                :key="event.id"
                                type="button"
                                @click="viewEventDetails(event)"
                                :class="[
                                    'w-full text-left truncate rounded px-1.5 py-0.5 text-[11px] font-semibold border flex items-center gap-1 transition-all',
                                    getCategoryBadgeClass(event.color)
                                ]"
                            >
                                <span class="h-1.5 w-1.5 rounded-full shrink-0" :class="getCategoryDotClass(event.color)" />
                                <span class="truncate">{{ event.title }}</span>
                            </button>

                            <!-- More events indicator -->
                            <button
                                v-if="cell.events.length > 3"
                                type="button"
                                @click="activeView = 'agenda'"
                                class="text-[10px] font-bold text-primary hover:underline block text-left px-1"
                            >
                                +{{ cell.events.length - 3 }} more
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIEW 2: AGENDA LIST VIEW -->
            <div v-else class="space-y-4">
                <div v-if="filteredEvents.length === 0" class="rounded-xl border border-dashed border-border bg-card p-12 text-center">
                    <CalendarDays class="mx-auto h-12 w-12 text-muted-foreground/50" />
                    <h3 class="mt-3 text-base font-bold text-foreground">No events scheduled</h3>
                    <p class="text-sm text-muted-foreground mt-1">There are no events matching your criteria for this month.</p>
                    <Button @click="openCreateModal()" class="mt-4 gap-1.5" size="sm">
                        <Plus class="h-4 w-4" />
                        Create First Event
                    </Button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Card
                        v-for="event in filteredEvents"
                        :key="event.id"
                        class="rounded-xl border border-border shadow-sm hover:border-primary/40 transition-all flex flex-col justify-between"
                    >
                        <CardHeader class="pb-3">
                            <div class="flex items-start justify-between gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold border',
                                        getCategoryBadgeClass(event.color)
                                    ]"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="getCategoryDotClass(event.color)" />
                                    {{ event.category_label }}
                                </span>
                                <Badge v-if="event.section_name" variant="outline" class="text-[10px]">
                                    {{ event.section_name }}
                                </Badge>
                                <Badge v-else variant="secondary" class="text-[10px]">
                                    School-Wide
                                </Badge>
                            </div>
                            <CardTitle class="text-base font-bold text-foreground mt-2 line-clamp-2">
                                {{ event.title }}
                            </CardTitle>
                            <CardDescription class="flex items-center gap-1 text-xs text-muted-foreground mt-1 font-medium">
                                <Clock class="h-3.5 w-3.5" />
                                <span>{{ event.start_date }}</span>
                                <span v-if="event.end_date">&rarr; {{ event.end_date }}</span>
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="pt-0">
                            <p v-if="event.description" class="text-xs text-muted-foreground line-clamp-3 mb-3">
                                {{ event.description }}
                            </p>
                            <div class="pt-3 border-t border-border/60 flex items-center justify-between text-xs">
                                <span class="text-muted-foreground text-[11px]">By: {{ event.creator_name || 'Admin' }}</span>
                                <div class="flex items-center gap-1">
                                    <Button variant="ghost" size="sm" class="h-7 px-2 text-xs" @click="viewEventDetails(event)">
                                        Details
                                    </Button>
                                    <Button v-if="event.can_edit" variant="outline" size="sm" class="h-7 px-2 text-xs gap-1" @click="openEditModal(event)">
                                        <Pencil class="h-3 w-3" />
                                        Edit
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- MODAL 1: CREATE EVENT -->
        <Dialog :open="isCreateOpen" @update:open="isCreateOpen = $event">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle class="text-lg font-bold">New Calendar Event</DialogTitle>
                    <DialogDescription>
                        Post a new academic activity, holiday, or exam to the school calendar.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitCreate" class="space-y-4 mt-2">
                    <div class="space-y-1.5">
                        <Label for="create-title">Event Title <span class="text-destructive">*</span></Label>
                        <Input
                            id="create-title"
                            v-model="createForm.title"
                            placeholder="e.g. First Quarter Periodic Exams"
                            required
                        />
                        <p v-if="createForm.errors.title" class="text-xs text-destructive">{{ createForm.errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label for="create-category">Category <span class="text-destructive">*</span></Label>
                            <select
                                id="create-category"
                                v-model="createForm.category"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                    {{ cat.label }}
                                </option>
                            </select>
                            <p v-if="createForm.errors.category" class="text-xs text-destructive">{{ createForm.errors.category }}</p>
                        </div>

                        <!-- Section selection if section_event -->
                        <div v-if="createForm.category === 'section_event'" class="space-y-1.5">
                            <Label for="create-section">Section Scope <span class="text-destructive">*</span></Label>
                            <select
                                id="create-section"
                                v-model="createForm.section_id"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
                                required
                            >
                                <option :value="null" disabled>Select section...</option>
                                <option v-for="sec in sections" :key="sec.id" :value="sec.id">
                                    {{ sec.name }}
                                </option>
                            </select>
                            <p v-if="createForm.errors.section_id" class="text-xs text-destructive">{{ createForm.errors.section_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label for="create-start">Start Date <span class="text-destructive">*</span></Label>
                            <Input
                                id="create-start"
                                type="date"
                                v-model="createForm.start_date"
                                required
                            />
                            <p v-if="createForm.errors.start_date" class="text-xs text-destructive">{{ createForm.errors.start_date }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="create-end">End Date <span class="text-xs text-muted-foreground">(Optional)</span></Label>
                            <Input
                                id="create-end"
                                type="date"
                                v-model="createForm.end_date"
                                :min="createForm.start_date"
                            />
                            <p v-if="createForm.errors.end_date" class="text-xs text-destructive">{{ createForm.errors.end_date }}</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="create-desc">Description / Instructions <span class="text-xs text-muted-foreground">(Optional)</span></Label>
                        <textarea
                            id="create-desc"
                            v-model="createForm.description"
                            rows="3"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
                            placeholder="Add additional instructions, guidelines, or details..."
                        ></textarea>
                        <p v-if="createForm.errors.description" class="text-xs text-destructive">{{ createForm.errors.description }}</p>
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="isCreateOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="createForm.processing">Save Event</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- MODAL 2: EVENT DETAILS -->
        <Dialog :open="isDetailOpen" @update:open="isDetailOpen = $event">
            <DialogContent v-if="selectedEvent" class="sm:max-w-[450px]">
                <DialogHeader>
                    <div class="flex items-center gap-2 mb-1">
                        <span
                            :class="[
                                'inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold border',
                                getCategoryBadgeClass(selectedEvent.color)
                            ]"
                        >
                            <span class="h-1.5 w-1.5 rounded-full" :class="getCategoryDotClass(selectedEvent.color)" />
                            {{ selectedEvent.category_label }}
                        </span>
                        <Badge v-if="selectedEvent.section_name" variant="outline" class="text-xs">
                            {{ selectedEvent.section_name }}
                        </Badge>
                    </div>
                    <DialogTitle class="text-xl font-bold">{{ selectedEvent.title }}</DialogTitle>
                </DialogHeader>

                <div class="space-y-3 py-2 text-sm">
                    <div class="flex items-center gap-2 text-muted-foreground font-medium">
                        <Clock class="h-4 w-4 text-primary" />
                        <span>{{ selectedEvent.start_date }}</span>
                        <span v-if="selectedEvent.end_date">&rarr; {{ selectedEvent.end_date }}</span>
                    </div>

                    <div v-if="selectedEvent.description" class="rounded-lg bg-muted/40 p-3 text-foreground leading-relaxed text-xs">
                        {{ selectedEvent.description }}
                    </div>

                    <div class="text-xs text-muted-foreground pt-2 border-t border-border flex items-center justify-between">
                        <span>Created by: <strong>{{ selectedEvent.creator_name || 'Admin' }}</strong></span>
                        <span>Scope: <strong>{{ selectedEvent.section_name || 'School-Wide' }}</strong></span>
                    </div>
                </div>

                <DialogFooter class="flex items-center justify-between gap-2 pt-2">
                    <div v-if="selectedEvent.can_edit" class="flex items-center gap-2">
                        <Button variant="outline" size="sm" class="gap-1 text-destructive hover:bg-destructive/10" @click="confirmDelete(selectedEvent)">
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete
                        </Button>
                        <Button variant="outline" size="sm" class="gap-1" @click="openEditModal(selectedEvent)">
                            <Pencil class="h-3.5 w-3.5" />
                            Edit
                        </Button>
                    </div>
                    <Button variant="secondary" size="sm" @click="isDetailOpen = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- MODAL 3: EDIT EVENT -->
        <Dialog :open="isEditOpen" @update:open="isEditOpen = $event">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle class="text-lg font-bold">Edit Calendar Event</DialogTitle>
                    <DialogDescription>Modify event details and dates.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitEdit" class="space-y-4 mt-2">
                    <div class="space-y-1.5">
                        <Label for="edit-title">Event Title <span class="text-destructive">*</span></Label>
                        <Input id="edit-title" v-model="editForm.title" required />
                        <p v-if="editForm.errors.title" class="text-xs text-destructive">{{ editForm.errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label for="edit-category">Category <span class="text-destructive">*</span></Label>
                            <select
                                id="edit-category"
                                v-model="editForm.category"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                    {{ cat.label }}
                                </option>
                            </select>
                        </div>

                        <div v-if="editForm.category === 'section_event'" class="space-y-1.5">
                            <Label for="edit-section">Section Scope <span class="text-destructive">*</span></Label>
                            <select
                                id="edit-section"
                                v-model="editForm.section_id"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
                                required
                            >
                                <option :value="null" disabled>Select section...</option>
                                <option v-for="sec in sections" :key="sec.id" :value="sec.id">
                                    {{ sec.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label for="edit-start">Start Date <span class="text-destructive">*</span></Label>
                            <Input id="edit-start" type="date" v-model="editForm.start_date" required />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="edit-end">End Date</Label>
                            <Input id="edit-end" type="date" v-model="editForm.end_date" :min="editForm.start_date" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="edit-desc">Description</Label>
                        <textarea
                            id="edit-desc"
                            v-model="editForm.description"
                            rows="3"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring"
                        ></textarea>
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="isEditOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="editForm.processing">Save Changes</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- MODAL 4: DELETE CONFIRMATION -->
        <Dialog :open="isDeleteConfirmOpen" @update:open="isDeleteConfirmOpen = $event">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle class="text-lg font-bold text-destructive flex items-center gap-2">
                        <AlertCircle class="h-5 w-5" />
                        Delete Event?
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete "<strong>{{ selectedEvent?.title }}</strong>"? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="pt-4">
                    <Button variant="outline" @click="isDeleteConfirmOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="submitDelete()">Delete Event</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
