<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle, Shield } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { create, index, store } from '@/routes/roles';

type Permission = {
    id: string;
    name: string;
};

const props = defineProps<{
    permissions: Record<string, Permission[]>;
    guards: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Roles',
                href: index.url(),
            },
            {
                title: 'Create',
                href: create.url(),
            },
        ],
    },
});

const selectedPermissions = ref<string[]>([]);

function formatPermissionAction(name: string): string {
    const parts = name.split('.');
    const action = parts[1] ?? parts[0];
    return action.charAt(0).toUpperCase() + action.slice(1).replace(/-/g, ' ');
}

function formatResourceName(resource: string): string {
    return resource.charAt(0).toUpperCase() + resource.slice(1).replace(/-/g, ' ');
}

function isGroupFullySelected(group: Permission[]): boolean {
    return group.every((p) => selectedPermissions.value.includes(p.name));
}

function isGroupPartiallySelected(group: Permission[]): boolean {
    return group.some((p) => selectedPermissions.value.includes(p.name)) && !isGroupFullySelected(group);
}

function toggleGroup(group: Permission[], checked: boolean | string) {
    if (checked) {
        const toAdd = group.map((p) => p.name).filter((n) => !selectedPermissions.value.includes(n));
        selectedPermissions.value.push(...toAdd);
    } else {
        const names = group.map((p) => p.name);
        selectedPermissions.value = selectedPermissions.value.filter((n) => !names.includes(n));
    }
}

function togglePermission(name: string, checked: boolean | string) {
    if (checked) {
        if (!selectedPermissions.value.includes(name)) {
            selectedPermissions.value.push(name);
        }
    } else {
        selectedPermissions.value = selectedPermissions.value.filter((n) => n !== name);
    }
}

const totalSelected = computed(() => selectedPermissions.value.length);
const totalPermissions = computed(() =>
    Object.values(props.permissions).reduce((sum, group) => sum + group.length, 0),
);
</script>

<template>
    <Head title="Create Role" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="sm" as-child>
                <Link :href="index.url()">
                    <ArrowLeft class="size-4" />
                    Back to Roles
                </Link>
            </Button>
        </div>

        <Heading title="Create Role" description="Define a new role and assign permissions" />

        <Form
            v-bind="store.form()"
            :reset-on-success="true"
            v-slot="{ errors, processing }"
            class="space-y-6"
        >
            <input
                v-for="permission in selectedPermissions"
                :key="permission"
                type="hidden"
                name="permissions[]"
                :value="permission"
            />

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Role Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            placeholder="e.g. editor, moderator"
                            required
                            autofocus
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="guard_name">Guard</Label>
                        <Select name="guard_name" :default-value="guards[0]">
                            <SelectTrigger id="guard_name">
                                <SelectValue placeholder="Select a guard" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="guard in guards"
                                    :key="guard"
                                    :value="guard"
                                >
                                    {{ guard }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.guard_name" />
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <Shield class="size-4" />
                                Permissions
                            </div>
                            <Badge variant="secondary">
                                {{ totalSelected }} / {{ totalPermissions }} selected
                            </Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div
                            v-for="(group, resource) in permissions"
                            :key="resource"
                            class="space-y-2"
                        >
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    :id="`group-${resource}`"
                                    :model-value="isGroupFullySelected(group)"
                                    :indeterminate="isGroupPartiallySelected(group)"
                                    @update:model-value="(checked) => toggleGroup(group, checked)"
                                />
                                <Label
                                    :for="`group-${resource}`"
                                    class="cursor-pointer text-sm font-semibold capitalize"
                                >
                                    {{ formatResourceName(resource as string) }}
                                </Label>
                                <Badge variant="outline" class="ml-auto text-xs">
                                    {{ group.filter((p) => selectedPermissions.includes(p.name)).length }}
                                    / {{ group.length }}
                                </Badge>
                            </div>

                            <div class="ml-6 grid gap-2 sm:grid-cols-2">
                                <div
                                    v-for="permission in group"
                                    :key="permission.id"
                                    class="flex items-center gap-2"
                                >
                                    <Checkbox
                                        :id="`perm-${permission.id}`"
                                        :model-value="selectedPermissions.includes(permission.name)"
                                        @update:model-value="(checked) => togglePermission(permission.name, checked)"
                                    />
                                    <Label
                                        :for="`perm-${permission.id}`"
                                        class="cursor-pointer text-sm font-normal"
                                    >
                                        {{ formatPermissionAction(permission.name) }}
                                    </Label>
                                </div>
                            </div>
                        </div>

                        <p v-if="Object.keys(permissions).length === 0" class="text-muted-foreground text-sm">
                            No permissions available.
                        </p>

                        <InputError :message="errors.permissions" />
                    </CardContent>
                </Card>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">
                    <LoaderCircle v-if="processing" class="size-4 animate-spin" />
                    <Shield v-else class="size-4" />
                    Create Role
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="index.url()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
