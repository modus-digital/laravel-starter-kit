<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Modules\Mailgun\EmailStatus;
use App\Enums\RBAC\Permission;
use App\Filament\Pages\MailgunAnalytics\MailgunStatsWidget;
use App\Models\Modules\Mailgun\EmailMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class MailgunAnalytics extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.mailgun-analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 21;

    protected static ?string $slug = 'monitoring/mailgun';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::MANAGE_SETTINGS) ?? false
            && config('modules.mailgun_analytics.enabled', false);
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.logs_and_monitoring');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.labels.mailgun');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::MANAGE_SETTINGS) ?? false
            && config('modules.mailgun_analytics.enabled', false);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('to_address')
                    ->label(__('admin.mailgun.table.recipient'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label(__('admin.mailgun.table.subject'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (EmailMessage $record): string => $record->subject)
                    ->sortable(),

                TextColumn::make('mailable_class')
                    ->label(__('admin.mailgun.table.mailable'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('admin.mailgun.table.status'))
                    ->badge()
                    ->formatStateUsing(fn (EmailStatus $state): string => $state->getLabel())
                    ->color(fn (EmailStatus $state): string => $state->getColor())
                    ->sortable(),

                TextColumn::make('latest_event')
                    ->label(__('admin.mailgun.table.last_event'))
                    ->formatStateUsing(function (EmailMessage $record): string {
                        $latestEvent = $record->latestEvent();

                        return $latestEvent ? $latestEvent->event_type->getLabel() : '-';
                    })
                    ->badge()
                    ->color(function (EmailMessage $record): string {
                        $latestEvent = $record->latestEvent();

                        return $latestEvent ? $latestEvent->event_type->getColor() : 'gray';
                    }),

                TextColumn::make('sent_at')
                    ->label(__('admin.mailgun.table.sent_at'))
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.mailgun.filters.status'))
                    ->options(fn (): array => collect(EmailStatus::cases())
                        ->mapWithKeys(fn (EmailStatus $status): array => [$status->value => $status->getLabel()])
                        ->all())
                    ->multiple(),

                Filter::make('sent_at')
                    ->label(__('admin.mailgun.filters.date_range'))
                    ->form([
                        DatePicker::make('sent_from')
                            ->label(__('admin.mailgun.filters.from')),
                        DatePicker::make('sent_to')
                            ->label(__('admin.mailgun.filters.to')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sent_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sent_at', '>=', $date),
                            )
                            ->when(
                                $data['sent_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sent_at', '<=', $date),
                            );
                    }),

                Filter::make('recipient')
                    ->label(__('admin.mailgun.filters.recipient'))
                    ->form([
                        TextInput::make('email')
                            ->label(__('admin.mailgun.filters.email'))
                            ->email(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['email'],
                            fn (Builder $query, $email): Builder => $query->where('to_address', 'like', "%{$email}%"),
                        );
                    }),

                SelectFilter::make('mailable_class')
                    ->label(__('admin.mailgun.filters.mailable'))
                    ->options(fn (): array => EmailMessage::query()
                        ->whereNotNull('mailable_class')
                        ->distinct()
                        ->pluck('mailable_class')
                        ->mapWithKeys(fn (string $class): array => [$class => class_basename($class)])
                        ->all())
                    ->searchable(),
            ])
            ->defaultSort('sent_at', 'desc')
            ->recordActions([
                Action::make('view_timeline')
                    ->label(__('admin.mailgun.actions.view_timeline'))
                    ->icon(Heroicon::OutlinedClock)
                    ->color('gray')
                    ->modalHeading(fn (EmailMessage $record): string => __('admin.mailgun.modal.heading', ['subject' => $record->subject]))
                    ->modalWidth('4xl')
                    ->slideOver()
                    ->modalContent(fn (EmailMessage $record): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view('filament.pages.mailgun-analytics.email-timeline', [
                        'emailMessage' => $record->load('events'),
                    ])),
            ])
            ->recordAction('view_timeline');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MailgunStatsWidget::class,
        ];
    }

    protected function getTableQuery(): Builder
    {
        return EmailMessage::query()
            ->with('events')
            ->latest('sent_at');
    }
}
