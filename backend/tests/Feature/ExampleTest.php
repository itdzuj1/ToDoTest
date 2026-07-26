<?php

it('returns a successful response from the application', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertExactJson([
            'name' => 'ToDoTest API',
        ]);
});
