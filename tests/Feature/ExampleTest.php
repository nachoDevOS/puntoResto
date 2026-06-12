<?php

test('the home page shows the entry page', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/Login'));
});
