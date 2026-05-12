<?php

declare(strict_types=1);

use App\Core\Exceptions\StaleModelException;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

it('has the required multi-tenant and audit columns on the users table', function (): void {
    $columns = ['tenant_id', 'created_by', 'updated_by', 'deleted_by', 'deleted_at', 'version'];

    foreach ($columns as $column) {
        expect(Schema::hasColumn('users', $column))->toBeTrue("missing column: {$column}");
    }
});

it('soft-deletes instead of hard-deleting', function (): void {
    $user = User::factory()->create();
    $id = $user->id;

    $user->delete();

    expect(User::find($id))->toBeNull();
    expect(User::withTrashed()->find($id))->not->toBeNull();
    expect(User::withTrashed()->find($id)->deleted_at)->not->toBeNull();
});

it('auto-fills created_by and updated_by when an authenticated user creates a row', function (): void {
    $actor = User::factory()->create();
    $this->actingAs($actor);

    $created = User::factory()->create();

    expect($created->created_by)->toBe($actor->id);
    expect($created->updated_by)->toBe($actor->id);
});

it('auto-fills updated_by on update', function (): void {
    $actor = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($actor);
    $other->name = 'Renamed';
    $other->save();

    expect($other->fresh()->updated_by)->toBe($actor->id);
});

it('sets deleted_by on soft delete', function (): void {
    $actor = User::factory()->create();
    $victim = User::factory()->create();

    $this->actingAs($actor);
    $victim->delete();

    expect(User::withTrashed()->find($victim->id)->deleted_by)->toBe($actor->id);
});

it('leaves audit columns null when no user is authenticated', function (): void {
    $user = User::factory()->create();

    expect($user->created_by)->toBeNull();
    expect($user->updated_by)->toBeNull();
});

it('starts version at 1 and increments on each update', function (): void {
    $user = User::factory()->create();
    expect($user->version)->toBe(1);

    $user->name = 'First change';
    $user->save();
    expect($user->fresh()->version)->toBe(2);

    $reloaded = User::find($user->id);
    $reloaded->name = 'Second change';
    $reloaded->save();
    expect($reloaded->fresh()->version)->toBe(3);
});

it('throws StaleModelException when two instances race to update', function (): void {
    $user = User::factory()->create();

    $instanceA = User::find($user->id);
    $instanceB = User::find($user->id);

    $instanceA->name = 'A wins';
    $instanceA->save();

    $instanceB->name = 'B loses';
    $instanceB->save();
})->throws(StaleModelException::class);

it('does not bump version when save is called with no changes', function (): void {
    $user = User::factory()->create();
    $before = $user->version;

    $user->save();

    expect($user->fresh()->version)->toBe($before);
});
