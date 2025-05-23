<?php

arch('App folder does not contain dd, die, dump, or var_dump')
    ->expect('App')
    ->not->toUse(['dd', 'die', 'dump', 'var_dump']);

arch('Should be succeed to the php preset')
    ->preset()->php();

arch('Should be succeed to the security preset')
    ->preset()->security();
