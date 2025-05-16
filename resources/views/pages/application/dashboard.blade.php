<?php

use function Laravel\Folio\name;

name('application.dashboard');


?>

<x-layouts.app title="{{ __('pages.dashboard.title') }}">
  <h1 class="text-3xl font-bold dark:text-white font-clash-display">
    {{ __('pages.dashboard.welcome', ['name' => auth()->user()->name]) }}
  </h1>
  <p class="text-xl mt-1 text-neutral-500 max-w-lg leading-6">
    {{ __('pages.dashboard.description') }}
  </p>
</x-layouts.app>
