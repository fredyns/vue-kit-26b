<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle, Users } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create, index, store } from '@/routes/users';

type Role = {
    id: string;
    name: string;
};

const props = defineProps<{
    roles: Role[];
}>();

const selectedRoles = ref<string[]>([]);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: index.url(),
            },
            {
                title: 'Create',
                href: create.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Create User" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="sm" as-child>
                <Link :href="index.url()">
                    <ArrowLeft class="size-4" />
                    Back to Users
                </Link>
            </Button>
        </div>

        <Heading title="Create User" description="Create a new user account" />

        <Form
            v-bind="store.form()"
            :reset-on-success="true"
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
                        required
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        placeholder="Password"
                        required
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm password"
                        required
                    />
                    <InputError :message="errors.password_confirmation" />
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
                                :name="`roles[${role.name}]`"
                                :value="role.name"
                                :checked="selectedRoles.includes(role.name)"
                                @update:checked="
                                    (checked) => {
                                        if (checked) {
                                            selectedRoles.push(role.name);
                                        } else {
                                            selectedRoles =
                                                selectedRoles.filter(
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
                    <Users v-else class="size-4" />
                    Create User
                </Button>
            </div>
        </Form>
    </div>
</template>
