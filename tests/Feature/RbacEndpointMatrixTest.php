<?php

use App\Models\User;

function rbacRoles(): array
{
    return [
        'admin',
        'admin-kabupaten',
        'ketua-timpembina-posyandu',
        'kabid',
        'admin-kecamatan',
        'kades',
        'bu-kades',
        'ketua-posyandu',
        'operator-desa',
        'kader',
        'masyarakat',
    ];
}

function rbacEndpoints(): array
{
    return [
        'dashboard' => [
            'url' => '/dashboard',
            'allowed' => [
                'admin',
                'admin-kabupaten',
                'ketua-timpembina-posyandu',
                'kabid',
                'admin-kecamatan',
                'kades',
                'bu-kades',
                'ketua-posyandu',
                'masyarakat',
            ],
            'redirect' => [
                'kader' => '/ajuan',
                'operator-desa' => '/admin/users',
            ],
        ],
        'admin.users' => [
            'url' => '/admin/users',
            'allowed' => ['admin', 'admin-kabupaten', 'operator-desa', 'kader'],
            'redirect' => [],
        ],
        'admin.posyandu' => [
            'url' => '/admin/posyandu',
            'allowed' => ['admin', 'admin-kabupaten', 'ketua-timpembina-posyandu', 'admin-kecamatan', 'operator-desa'],
            'redirect' => [],
        ],
        'admin.settings' => [
            'url' => '/admin/settings',
            'allowed' => ['admin', 'admin-kabupaten'],
            'redirect' => [],
        ],
        'ketua-posyandu.takeover' => [
            'url' => '/ketua-posyandu/takeover',
            'allowed' => ['ketua-posyandu'],
            'redirect' => [],
        ],
        'buku_saku' => [
            'url' => '/buku_saku',
            'allowed' => rbacRoles(),
            'redirect' => [],
        ],
        'ajuan' => [
            'url' => '/ajuan',
            'allowed' => [
                'ketua-timpembina-posyandu',
                'admin-kabupaten',
                'kabid',
                'admin-kecamatan',
                'kades',
                'bu-kades',
                'ketua-posyandu',
                'operator-desa',
                'kader',
                'masyarakat',
            ],
            'redirect' => [],
        ],
        'profile' => [
            'url' => '/profile',
            'allowed' => rbacRoles(),
            'redirect' => [],
        ],
    ];
}

dataset('rbac.role-endpoint.matrix', function () {
    $matrix = [];

    foreach (rbacRoles() as $role) {
        foreach (rbacEndpoints() as $endpointKey => $endpoint) {
            $expected = in_array($role, $endpoint['allowed'], true) ? 'ok' : 'forbidden';
            $redirectTo = $endpoint['redirect'][$role] ?? null;

            if ($redirectTo !== null) {
                $expected = 'redirect';
            }

            $matrix["{$role} -> {$endpointKey}"] = [
                $role,
                $endpoint['url'],
                $expected,
                $redirectTo,
            ];
        }
    }

    return $matrix;
});

dataset('rbac.protected.endpoints', function () {
    return array_map(
        fn(array $endpoint) => [$endpoint['url']],
        array_values(rbacEndpoints())
    );
});

test('RBAC role x endpoint matrix is enforced', function (
    string $role,
    string $url,
    string $expected,
    ?string $redirectTo
) {
    $user = User::factory()->create([
        'role' => $role,
    ]);

    $response = $this->actingAs($user)->get($url);

    if ($expected === 'ok') {
        $response->assertOk();
        return;
    }

    if ($expected === 'forbidden') {
        $response->assertForbidden();
        return;
    }

    $response->assertStatus(302);

    if ($redirectTo) {
        $response->assertRedirect($redirectTo);
    }
})->with('rbac.role-endpoint.matrix');

test('guest is redirected from protected matrix endpoints', function (string $url) {
    $this->get($url)
        ->assertRedirect(route('login'));
})->with('rbac.protected.endpoints');
