<?php

namespace Rezadaulay\FilamentWhatsappNotification\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rezadaulay\FilamentWhatsappNotification\FilamentWhatsappNotification
 */
class FilamentWhatsappNotification extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Rezadaulay\FilamentWhatsappNotification\FilamentWhatsappNotification::class;
    }
}
