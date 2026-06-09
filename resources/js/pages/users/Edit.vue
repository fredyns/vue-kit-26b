<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle, Save } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, show, update } from '@/routes/users';

type Role = {
    id: string;
    name: string;
};

type User = {
    id: string;
    name: string;
    email: string;
    roles: string[];
};

const props = defineProps<{
    user: User;
    roles: Role[];
}>();

const selectedRoles = ref<string[]>([...props.user.roles]);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: index.url(),
            },
            {
                title: 'Edit User',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="sm" as-child>
                <Link :href="show.url(user.id)">
                    <ArrowLeft class="size-4" />
                    Back to User
                </Link>
            </Button>
        </div>

        <Heading :title="`Edit ${user.name}`" description="Update user account details" />

        <Form
            v-bind="update.form(user.id)"
            v-slot="{ errors, processing }"
            class="max-w-xl space-y-6"
        >
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        placeholder="Full name"
                        :default-value="user.name"
                        required
                        autofocus
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="email@example.com"
                        :default-value="user.email"
                        required
                    />
                    <InputError :message="errors.email" />
                </div>

                <div v-if="roles.length > 0" class="space-y-3">
                    <Label>Roles</Label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div
                            v-for="role in roles"
                            :key="role.id"
                            class="flex items-center space-x-2"
                        >
                            <Checkbox
                                :id="`role-${role.id}`"
                                name="roles[]"
                                :value="role.name"
                                :model-value="selectedRoles.includes(role.name)"
                                @update:model-value="
                                    (checked) => {
                                        if (checked) {
                                            selectedRoles.push(role.name);
                                        } else {
                                            selectedRoles = selectedRoles.filter(
                                                (r) => r !== role.name,
                                            );
                                        }
                                    }
                                "
                            />
                            <Label
                                :for="`role-${role.id}`"
                                class="cursor-pointer text-sm font-normal"
                            >
                                {{ role.name }}
                            </Label>
                        </div>
                    </div>
                    <InputError :message="errors.roles" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">
                    <LoaderCircle v-if="processing" class="size-4 animate-spin" />
                    <Save v-else class="size-4" />
                    Save Changes
                </Button>

                <Button variant="outline" as-child>
                    <Link :href="show.url(user.id)">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
