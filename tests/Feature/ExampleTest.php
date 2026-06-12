<?php

use App\Models\User;

test('the home page redirects authenticated users to the pos', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect('/pos');
});
