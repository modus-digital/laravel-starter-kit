<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    #region UI Configuration

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    protected static ?string $model = User::class;

    /**
     * The icon of the resource.
     *
     * @var string|null
     */
    protected static ?string $navigationIcon = 'heroicon-o-users';

    /**
     * The text for the navigation label.
     *
     * @var string|null
     */
    protected static ?string $navigationLabel = 'Gebruikers';

    /**
     * The slug for the resource
     * 
     * @var string|null
     */
    protected static ?string $slug = '/users';

    /**
     * The label for this resource.
     *
     * @return string
     */
    public static function getModelLabel(): string
    {
        return 'Gebruiker';
    }

    /**
     * The plural label for this resource.
     *
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return 'Gebruikers';
    }

    #endregion

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Persoonlijke gegevens')
                    ->description('Hier bewerk je de persoonlijke informatie van deze gebruiker.')
                    ->aside()
                    ->columns(3)
                    ->schema([

                        Forms\Components\TextInput::make('first_name')
                            ->label('Voornaam')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('last_name_prefix')
                            ->label('Tussenvoegsel')
                            ->maxLength(255),
        
                        Forms\Components\TextInput::make('last_name')
                            ->label('Achternaam')
                            ->maxLength(255),
        
                        Forms\Components\TextInput::make('email')
                            ->label('E-mailadres')
                            ->columnSpan(2)
                            ->required()
                            ->email()
                            ->maxLength(255),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Column for name
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Volledige naam')
                    ->formatStateUsing(function (User $record): string {
                        return "{$record->first_name} {$record?->last_name_prefix} {$record?->last_name}";
                    })
                    ->searchable()
                    ->sortable(),

                // Column for email
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mailadres')
                    ->searchable()
                    ->sortable(),

                // Column for displaying role
                Tables\Columns\TextColumn::make('')
                    ->label('Rol')
                    ->getStateUsing(function (User $record): ?string {
                        return $record->roles->first()->name ?? 'Geen rol';
                    })
                    ->badge(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
