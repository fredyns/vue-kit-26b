<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Shield, ShieldCheck, Trash2, Pencil } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { edit, index } from '@/routes/roles';

type Permission = {
    id: string;
    name: string;
};

type Role = {
    id: string;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
    permissions: Permission[];
    is_protected: boolean;
};

type Can = {
    update: boolean;
    delete: boolean;
};

const props = defineProps<{
    role: Role;
    can: Can;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Roles',
                href: index.url(),
            },
            {
                title: 'View Role',
                href: '#',
            },
        ],
    },
});

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function deleteRole() {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
        // router.delete(route('roles.destroy', props.role.id));
    }
}

function formatPermissionName(name: string): string {
    return name
        .split('.')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function groupPermissionsByResource(permissions: Permission[]): Record<string, Permission[]> {
    return permissions.reduce((groups, permission) => {
        const resource = permission.name.split('.')[0] || 'other';

        if (!groups[resource]) {
            groups[resource] = [];
        }

        groups[resource].push(permission);

        return groups;
    }, {} as Record<string, Permission[]>);
}

const groupedPermissions = groupPermissionsByResource(props.role.permissions);
</script>

<template>
    <Head :title="role.name" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="sm" as-child>
                <Link :href="index.url()">
                    <ArrowLeft class="size-4" />
                    Back to Roles
                </Link>
            </Button>
        </div>

        <div class="flex items-center justify-between">
            <div>
                <Heading :title="role.name" description="Role details and permissions" />
                <div class="mt-2 flex items-center gap-2">
                    <Badge variant="outline">{{ role.guard_name }}</Badge>
                    <Badge :variant="role.is_protected ? 'secondary' : 'outline'">
                        {{ role.is_protected ? 'Protected' : 'Custom' }}
                    </Badge>
                </div>
            </div>

            <div class="flex gap-2">
                <Button v-if="can.update" variant="outline" as-child>
                    <Link :href="edit.url({ role: role.id })">
                        <Pencil class="size-4" />
                        Edit
                    </Link>
                </Button>
                <Button
                    v-if="can.delete"
                    variant="destructive"
                    @click="deleteRole"
                >
                    <Trash2 class="size-4" />
                    Delete
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Shield class="size-4" />
                        Role Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Name</p>
                        <p class="font-medium">{{ role.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Guard</p>
                        <Badge variant="outline">{{ role.guard_name }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Type</p>
                        <Badge :variant="role.is_protected ? 'secondary' : 'outline'">
                            {{ role.is_protected ? 'Protected' : 'Custom' }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="size-4" />
                        Timeline
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Created</p>
                        <p class="font-medium">{{ formatDate(role.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Last Updated</p>
                        <p class="font-medium">{{ formatDate(role.updated_at) }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card class="md:col-span-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <ShieldCheck class="size-4" />
                        Permissions
                        <Badge variant="secondary">{{ role.permissions.length }}</Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="role.permissions.length === 0" class="text-muted-foreground text-sm">
                        No permissions assigned to this role.
                    </div>
                    <div v-else class="space-y-6">
                        <div
                            v-for="(permissions, resource) in groupedPermissions"
                            :key="resource"
                            class="space-y-2"
                        >
                            <h4 class="text-sm font-semibold capitalize">
                                {{ resource }}
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    variant="outline"
                                >
                                    {{ formatPermissionName(permission.name) }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
