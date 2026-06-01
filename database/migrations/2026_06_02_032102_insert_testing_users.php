<?php

return new class extends \App\DB\BaseInsertCollectionMigration {
    public string $fileName = 'users.testing.php';
    public string $table = 'users';
    public string $uniqueColumn = 'email';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $env = config('app.env');
        $production = str_contains($env, 'prod');
        if ($production) return;

        parent::up();
    }

    public function down(): void
    {
        $env = config('app.env');
        $production = str_contains($env, 'prod');
        if ($production) return;

        parent::down();
    }
};
