<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\RBAC\Permission;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;

final class Notifications extends Page implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'notifications';

    protected string $view = 'filament.pages.notifications';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function getNavigationLabel(): string
    {
        return __('Notifications');
    }

    public function getHeading(): string
    {
        return __('Notifications');
    }

    public function table(Table $table): Table
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        assert($user !== null);

        return $table
            ->query(fn (): Builder => $user->notifications()->getQuery()->latest())
            ->poll('30s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('data.title')
                    ->label(__('Title'))
                    ->wrap()
                    ->searchable(),
                TextColumn::make('data.body')
                    ->label(__('Message'))
                    ->limit(120)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Received'))
                    ->since(),
                TextColumn::make('read_at')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? __('Read') : __('Unread'))
                    ->color(fn (?string $state): string => $state ? 'gray' : 'warning'),
            ])
            ->actions([
                Action::make('mark_read')
                    ->label(__('Mark as read'))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->visible(fn (DatabaseNotification $record): bool => $record->read_at === null)
                    ->action(fn (DatabaseNotification $record): DatabaseNotification => tap($record)->markAsRead()),
                Action::make('mark_unread')
                    ->label(__('Mark as unread'))
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->visible(fn (DatabaseNotification $record): bool => $record->read_at !== null)
                    ->action(fn (DatabaseNotification $record): DatabaseNotification => tap($record)->update(['read_at' => null])),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Action::make('mark_read_bulk')
                        ->label(__('Mark selected as read'))
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(fn (\Illuminate\Support\Collection $records): int => $records->filter(fn (DatabaseNotification $notification): bool => $notification->read_at === null)
                            ->each(fn (DatabaseNotification $notification): DatabaseNotification => tap($notification)->markAsRead())
                            ->count()),
                    Action::make('mark_unread_bulk')
                        ->label(__('Mark selected as unread'))
                        ->icon(Heroicon::OutlinedArrowUturnLeft)
                        ->action(fn (\Illuminate\Support\Collection $records): int => $records->filter(fn (DatabaseNotification $notification): bool => $notification->read_at !== null)
                            ->each(fn (DatabaseNotification $notification): DatabaseNotification => tap($notification)->update(['read_at' => null]))
                            ->count()),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
