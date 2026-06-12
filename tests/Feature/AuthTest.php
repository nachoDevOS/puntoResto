<?php

it('shows the entry page', function () {
    $this->get('/login')->assertOk();
});

it('enters the system without credentials', function () {
    $this->post('/login')->assertRedirect(route('pos.index'));

    $this->assertGuest();
});

it('allows guests to use protected pages freely', function () {
    $this->get('/pos')->assertOk();
});

it('keeps logout as a harmless redirect to the entry page', function () {
    $this->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
});
