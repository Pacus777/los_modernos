<?php

Broadcast::channel('admin-notifications', function ($user) {
    return $user?->tieneRol('admin') ?? false;
});
