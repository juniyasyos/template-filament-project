<?php

namespace App\Filament\Panel\Resources\Users\Pages;

use App\Filament\Panel\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
