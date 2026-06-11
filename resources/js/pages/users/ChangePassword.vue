<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, KeyRound, LoaderCircle } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, show, updatePassword } from '@/routes/users';

type User = {
    id: string;
    name: string;
};

const props = defineProps<{
    user: User;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: index.url(),
            },
            {
                title: 'View User',
                href: '#',
            },
            {
                title: 'Change Password',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Change Password — ${user.name}`" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="sm" as-child>
                <Link :href="show.url(user.id)">
                    <ArrowLeft class="size-4" />
                    Back to User
                </Link>
            </Button>
        </div>

        <Heading :title="`Change Password — ${user.name}`" description="Set a new password for this user" />

        <Form
            v-bind="updatePassword.form(user.id)"
            v-slot="{ errors, processing }"
            class="max-w-xl space-y-6"
        >
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="password">New Password</Label>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="New password"
                        required
                        autofocus
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="Confirm new password"
                        required
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">
                    <LoaderCircle v-if="processing" class="size-4 animate-spin" />
                    <KeyRound v-else class="size-4" />
                    Change Password
                </Button>

                <Button variant="outline" as-child>
                    <Link :href="show.url(user.id)">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
