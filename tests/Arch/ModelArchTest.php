<?php

arch('Models should be classes')
    ->expect('App\Models')
    ->toBeClasses();

arch('Models should be extending Eloquent Model')
    ->expect('App\Models')
    ->toUse('Illuminate\Database\Eloquent\Model');
