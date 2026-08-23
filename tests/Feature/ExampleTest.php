<?php

test('root route redirects to login', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});
