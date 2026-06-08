<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Mail, Shield, User } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index } from '@/routes/users';

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
    updated_at: string;
    roles: Role[];
};

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
        ],
    },
});

const props = defineProps<{
    user: User;
    can: {
        update: boolean;
        delete: boolean;
    };
}>();

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head :title="user.name" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center gap-4">
            <Button variant="outline" size="sm" as-child>
                <Link :href="index.url()">
                    <ArrowLeft class="size-4" />
                    Back to Users
                </Link>
            </Button>
        </div>

        <div class="flex items-center justify-between">
            <Heading :title="user.name" description="User details" />

            <div class="flex gap-2">
                <Button v-if="can.update" variant="outline" as-child>
                    <Link href="#">Edit</Link>
                </Button>
                <Button v-if="can.delete" variant="destructive" as-child>
                    <Link href="#">Delete</Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="size-4" />
                        Profile Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Name</p>
                        <p class="font-medium">{{ user.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Email</p>
                        <div class="flex items-center gap-2">
                            <Mail class="size-4 text-muted-foreground" />
                            <p class="font-medium">{{ user.email }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        <Badge
                            :variant="
                                user.email_verified_at ? 'default' : 'outline'
                            "
                        >
                            {{
                                user.email_verified_at
                                    ? 'Verified'
                                    : 'Unverified'
                            }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Shield class="size-4" />
                        Roles & Permissions
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Assigned Roles
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <Badge
                                v-for="role in user.roles"
                                :key="role.id"
                                variant="secondary"
                            >
                                {{ role.name }}
                            </Badge>
                            <p
                                v-if="user.roles.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                No roles assigned
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="size-4" />
                        Account Timeline
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Created</p>
                        <p class="font-medium">
                            {{ formatDate(user.created_at) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Last Updated
                        </p>
                        <p class="font-medium">
                            {{ formatDate(user.updated_at) }}
                        </p>
                    </div>
                    <div v-if="user.email_verified_at">
                        <p class="text-sm text-muted-foreground">
                            Email Verified
                        </p>
                        <p class="font-medium">
                            {{ formatDate(user.email_verified_at) }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
