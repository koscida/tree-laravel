<?php

/*
|--------------------------------------------------------------------------
| Navigation links
|--------------------------------------------------------------------------
|
| Each entry is rendered in the navbar, in order. "route" is a route name
| and "label" is the link text:
|
|   ['label' => 'Files', 'route' => 'files.index', 'active' => 'files.*'],
|
| "active" is an optional route pattern used for the highlighted state. It
| defaults to "route", so a link is only active on exactly that page.
|
*/

return [
    ['label' => 'Imports', 'route' => 'file_imports.index', 'active' => 'file_imports.*'],
    ['label' => 'Files', 'route' => 'files.index', 'active' => 'files.*'],
    ['label' => 'Settings', 'route' => 'file_settings.index', 'active' => 'file_settings.*'],
];
