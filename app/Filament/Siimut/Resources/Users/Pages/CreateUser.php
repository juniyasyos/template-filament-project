<?php

namespace App\Filament\Siimut\Resources\Users\Pages;

use App\Filament\Siimut\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
