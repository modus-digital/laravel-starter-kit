<?php

declare(strict_types=1);

it('returns a successful response', function () {
    $response = $this->get('/');

    // Root redirects to login or application page
    $response->assertRedirect();
});
