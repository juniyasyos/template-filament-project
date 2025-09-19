<?php

namespace App\Filament\Siimut\Resources\Users;

use App\Filament\Siimut\Resources\Users\Pages\CreateUser;
use App\Filament\Siimut\Resources\Users\Pages\EditUser;
use App\Filament\Siimut\Resources\Users\Pages\ListUsers;
use App\Filament\Siimut\Resources\Users\Schemas\UserForm;
use App\Filament\Siimut\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use juniyasyos\ShieldLite\Concerns\HasShieldLite;

class UserResource extends Resource
{
    use HasShieldLite; // Shield Lite integration

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static string|UnitEnum|null $navigationGroup = 'User Managements';
    protected static ?int $navigationSort = 1;

    /**
     * Define permissions - auto-generated in database
     */
    public function defineGates(): array
    {
        return [
            'users.viewAny' => 'View users list',
            'users.view' => 'View user details',
            'users.create' => 'Create new users',
            'users.update' => 'Update users',
            'users.delete' => 'Delete users',
            'users.restore' => 'Restore deleted users',
            'users.forceDelete' => 'Permanently delete users',
        ];
    }

    /**
     * Custom role name for permission generation
     */
    public function roleName(): string
    {
        return 'users';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
