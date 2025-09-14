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
use Hexters\HexaLite\HasHexaLite;

class UserResource extends Resource
{
    use HasHexaLite;
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'User Management';
    }

    protected static ?string $recordTitleAttribute = 'User';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public function defineGates(): array
    {
        return [
            'user.index' => __('Allows viewing users'),
            'user.create' => __('Allows creating users'),
            'user.update' => __('Allows updating users'),
            'user.delete' => __('Allows deleting users'),
        ];
    }

    public static function canAccess(): bool
    {
        return hexa()->can('user.index');
    }

    public static function canCreate(): bool
    {
        return hexa()->can('user.create');
    }

    public static function canEdit($record): bool
    {
        return hexa()->can('user.update');
    }

    public static function canDelete($record): bool
    {
        return hexa()->can('user.delete');
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
