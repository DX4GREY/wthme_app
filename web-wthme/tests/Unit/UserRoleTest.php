<?php

use App\Models\User;

it('treats admin as participant for portal access', function () {
    $admin = new User;
    $admin->role = 'admin';

    expect($admin->isPeserta())->toBeTrue();
});
