<?php

test('registration is disabled and returns 404', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('default root page redirects to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
