<?php

use App\Models\User;

function masterDataUser(string $role): User
{
    return $role === 'super_admin'
        ? User::factory()->superAdmin()->create()
        : User::factory()->create(['role' => 'admin', 'status' => 'approved']);
}

it('blocks admins from every master data page', function (string $path) {
    $this->actingAs(masterDataUser('admin'))
        ->get($path)
        ->assertForbidden();
})->with([
    '/instansi',
    '/kategori',
    '/jenis',
    '/inventaris',
]);

it('allows super admins to open every master data page', function (string $path) {
    $this->actingAs(masterDataUser('super_admin'))
        ->get($path)
        ->assertOk();
})->with([
    '/instansi',
    '/kategori',
    '/jenis',
    '/inventaris',
]);

it('only shows master data and user management menus to super admins', function () {
    $adminResponse = $this->actingAs(masterDataUser('admin'))->get('/profile');

    $adminResponse->assertOk()
        ->assertDontSee('Master Data')
        ->assertDontSee('Manajemen Admin');

    $superAdminResponse = $this->actingAs(masterDataUser('super_admin'))->get('/profile');

    $superAdminResponse->assertOk()
        ->assertSee('Master Data')
        ->assertSee('Manajemen Admin');
});

it('keeps admins out of the dashboard links to master data', function () {
    // Dashboard memakai DATE_FORMAT() yang hanya tersedia di MySQL, jadi
    // perilaku tautan diverifikasi lewat markup kartu yang dirender.
    $dashboardView = file_get_contents(resource_path('views/admin/dashboard.blade.php'));

    expect($dashboardView)
        ->toContain("\$canManageMasterData")
        ->toContain("'route' => \$canManageMasterData ? 'inventaris.index' : null")
        ->toContain("'route' => \$canManageMasterData ? 'instansi.index' : null")
        ->toContain("'route' => \$canManageMasterData ? 'kategori.index' : null");
});
