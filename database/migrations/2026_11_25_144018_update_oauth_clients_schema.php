<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE TABLE oauth_clients_v12 LIKE oauth_clients;');
        DB::statement('INSERT INTO oauth_clients_v12 SELECT * FROM oauth_clients;');

        DB::statement('UPDATE oauth_clients SET user_id = NULL;');

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->nullableMorphs('owner', after: 'user_id');

            $table->after('provider', function (Blueprint $table) {
                $table->text('redirect_uris')->nullable();
                $table->text('grant_types')->nullable();
            });
        });

        foreach (Passport::client()->cursor() as $client) {
            Model::withoutTimestamps(fn() => $client->forceFill([
                'owner_id' => $client->user_id,
                'owner_type' => $client->user_id
                    ? config('auth.providers.' . ($client->provider ?: config('auth.guards.api.provider')) . '.model')
                    : null,
                'redirect_uris' => $client->redirect_uris,
                'grant_types' => $client->grant_types,
            ])->save());
        }

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'redirect', 'personal_access_client', 'password_client']);

            $table->text('redirect_uris')->nullable(false)->change();
            $table->text('grant_types')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_clients');

        DB::statement('RENAME TABLE oauth_clients_v12 TO oauth_clients;');
    }
};
