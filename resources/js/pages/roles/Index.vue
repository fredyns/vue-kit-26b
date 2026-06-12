<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Search, Shield, ShieldCheck } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/roles';

type Role = {
    id: string;
    name: string;
    guard_name: string;
    created_at: string;
    permissions_count: number;
    is_protected: boolean;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedRoles = {
    data: Role[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

const props = defineProps<{
    roles: PaginatedRoles;
    guards: string[];
    filters: {
        search: string;
        guard: string;
    };
    can: {
        create: boolean;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Roles',
                href: index.url(),
            },
        ],
    },
});

const search = ref(props.filters.search);
const activeGuard = ref(props.filters.guard);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

function navigate() {
    router.get(
        index.url(),
        {
            search: search.value || undefined,
            guard: activeGuard.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

watch(search, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(navigate, 300);
});

function setGuard(guard: string) {
    activeGuard.value = activeGuard.value === guard ? '' : guard;
    navigate();
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Roles" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <Heading title="Roles" description="Manage roles and permissions" />

            <Button v-if="can.create" as-child>
                <Link href="#">
                    <Shield class="size-4" />
                    Create Role
                </Link>
            </Button>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative max-w-sm flex-1">
                <Search
                    class="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
                />
                <Input
                    v-model="search"
                    type="search"
                    placeholder="Search roles..."
                    class="pl-9"
                />
            </div>
            <div class="flex items-center gap-1.5">
                <Button
                    :variant="activeGuard === '' ? 'default' : 'outline'"
                    size="sm"
                    class="rounded-full"
                    @click="setGuard('')"
                >
                    All
                </Button>
                <Button
                    v-for="guard in guards"
                    :key="guard"
                    :variant="activeGuard === guard ? 'default' : 'outline'"
                    size="sm"
                    class="rounded-full"
                    @click="setGuard(guard)"
                >
                    {{ guard }}
                </Button>
            </div>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Guard</TableHead>
                        <TableHead>Permissions</TableHead>
                        <TableHead>Type</TableHead>
                        <TableHead>Created</TableHead>
                        <TableHead class="w-16">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableEmpty
                        v-if="roles.data.length === 0"
                        :colspan="6"
                    >
                        No roles found.
                    </TableEmpty>
                    <TableRow
                        v-for="role in roles.data"
                        :key="role.id"
                    >
                        <TableCell class="font-medium">
                            <Link :href="`/roles/${role.id}`" class="hover:underline">
                                {{ role.name }}
                            </Link>
                        </TableCell>
                        <TableCell>
                            <Badge variant="outline">{{ role.guard_name }}</Badge>
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center gap-1">
                                <ShieldCheck class="text-muted-foreground size-4" />
                                <span>{{ role.permissions_count }}</span>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="role.is_protected ? 'secondary' : 'outline'">
                                {{ role.is_protected ? 'Protected' : 'Custom' }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            {{ formatDate(role.created_at) }}
                        </TableCell>
                        <TableCell>
                            <div class="flex gap-1">
                                <Button variant="ghost" size="icon" as-child disabled>
                                    <span>
                                        <Pencil class="size-4" />
                                        <span class="sr-only">Edit role</span>
                                    </span>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            v-if="roles.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-muted-foreground text-sm">
                Showing page {{ roles.current_page }} of
                {{ roles.last_page }} ({{ roles.total }} total)
            </p>
            <div class="flex gap-1">
                <template
                    v-for="link in roles.links"
                    :key="link.label"
                >
                    <Button
                        v-if="link.url"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        as-child
                    >
                        <Link
                            :href="link.url"
                            preserve-state
                            v-html="link.label"
                        />
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        size="sm"
                        disabled
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
