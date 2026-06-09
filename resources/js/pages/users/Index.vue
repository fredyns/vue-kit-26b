<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Search, Users } from 'lucide-vue-next';
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
import { create, edit, index, show } from '@/routes/users';

type Role = {
    id: string;
    name: string;
};

type User = {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    web_roles: Role[];
    can: {
        view: boolean;
        update: boolean;
    };
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedUsers = {
    data: User[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

const props = defineProps<{
    users: PaginatedUsers;
    filters: {
        search: string;
    };
    can: {
        create: boolean;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: index.url(),
            },
        ],
    },
});

const search = ref(props.filters.search);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(search, (value) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        router.get(
            index.url(),
            { search: value || undefined },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 300);
});

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Users" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <Heading title="Users" description="Manage user accounts" />

            <Button v-if="can.create" as-child>
                <Link :href="create.url()">
                    <Users class="size-4" />
                    Create User
                </Link>
            </Button>
        </div>

        <div class="flex items-center gap-4">
            <div class="relative max-w-sm flex-1">
                <Search
                    class="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
                />
                <Input
                    v-model="search"
                    type="search"
                    placeholder="Search users..."
                    class="pl-9"
                />
            </div>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Roles</TableHead>
                        <TableHead>Verified</TableHead>
                        <TableHead>Created</TableHead>
                        <TableHead class="w-24">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableEmpty
                        v-if="users.data.length === 0"
                        :colspan="6"
                    >
                        No users found.
                    </TableEmpty>
                    <TableRow
                        v-for="user in users.data"
                        :key="user.id"
                    >
                        <TableCell class="font-medium">
                            <Link
                                v-if="user.can.view"
                                :href="show.url(user.id)"
                                class="hover:underline"
                            >
                                {{ user.name }}
                            </Link>
                            <span v-else>{{ user.name }}</span>
                        </TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="role in user.web_roles"
                                    :key="role.id"
                                    variant="secondary"
                                >
                                    {{ role.name }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    user.email_verified_at
                                        ? 'default'
                                        : 'outline'
                                "
                            >
                                {{
                                    user.email_verified_at
                                        ? 'Verified'
                                        : 'Unverified'
                                }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            {{ formatDate(user.created_at) }}
                        </TableCell>
                        <TableCell>
                            <div class="flex gap-1">
                                <Button v-if="user.can.view" variant="ghost" size="icon" as-child>
                                    <Link :href="show.url(user.id)">
                                        <Eye class="size-4" />
                                        <span class="sr-only">View user</span>
                                    </Link>
                                </Button>
                                <Button v-if="user.can.update" variant="ghost" size="icon" as-child>
                                    <Link :href="edit.url(user.id)">
                                        <Pencil class="size-4" />
                                        <span class="sr-only">Edit user</span>
                                    </Link>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            v-if="users.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-muted-foreground text-sm">
                Showing page {{ users.current_page }} of
                {{ users.last_page }} ({{ users.total }} total)
            </p>
            <div class="flex gap-1">
                <template
                    v-for="link in users.links"
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
