<?php

namespace App\Filament\PlatformAdmin\Resources;

use App\Filament\PlatformAdmin\Resources\PlatformSubscriptionResource\Pages;
use App\Models\PlatformSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlatformSubscriptionResource extends Resource
{
    protected static ?string $model = PlatformSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Subscriptions';

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Subscription')->schema([
                Forms\Components\Select::make('school_id')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'trial' => 'Trial',
                        'active' => 'Active',
                        'past_due' => 'Past Due',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ])
                    ->required(),

                Forms\Components\Select::make('billing_cycle')
                    ->options(['monthly' => 'Monthly', 'annual' => 'Annual'])
                    ->default('monthly')
                    ->required(),

                Forms\Components\Select::make('tier')
                    ->options([
                        1 => 'Tier 1 — Starter (≤100 students)',
                        2 => 'Tier 2 — Growth (101–400 students)',
                        3 => 'Tier 3 — Enterprise (401+ students)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('amount_kes')
                    ->label('Amount (KES)')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('student_count_at_billing')
                    ->label('Student Count at Billing')
                    ->numeric()
                    ->default(0),
            ])->columns(2),

            Forms\Components\Section::make('Billing Dates')->schema([
                Forms\Components\DateTimePicker::make('current_period_start'),
                Forms\Components\DateTimePicker::make('current_period_end'),
                Forms\Components\DateTimePicker::make('next_billing_date'),
                Forms\Components\DateTimePicker::make('last_payment_at'),
                Forms\Components\TextInput::make('last_payment_amount_kes')->label('Last Payment (KES)')->numeric(),
            ])->columns(2),

            Forms\Components\Section::make('PayStack')->schema([
                Forms\Components\TextInput::make('paystack_customer_code'),
                Forms\Components\TextInput::make('paystack_subscription_code'),
                Forms\Components\TextInput::make('paystack_authorization_code'),
            ])->columns(2),

            Forms\Components\Section::make('Notes')->schema([
                Forms\Components\Textarea::make('notes')->rows(3),
            ]),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name')
                    ->label('School')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'trial',
                        'success' => 'active',
                        'danger' => ['past_due', 'expired', 'cancelled'],
                    ]),

                Tables\Columns\TextColumn::make('tier')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'Starter',
                        2 => 'Growth',
                        3 => 'Enterprise',
                        default => '—',
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        1 => 'gray',
                        2 => 'info',
                        3 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount_kes')
                    ->label('KES/mo')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('student_count_at_billing')
                    ->label('Students')
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_billing_date')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->next_billing_date?->isPast() ? 'danger' : null),

                Tables\Columns\TextColumn::make('last_payment_at')
                    ->label('Last Payment')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'trial' => 'Trial',
                        'active' => 'Active',
                        'past_due' => 'Past Due',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('tier')
                    ->options([1 => 'Starter', 2 => 'Growth', 3 => 'Enterprise']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('charge')
                    ->label('Charge Now')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (PlatformSubscription $record) => app(\App\Http\Controllers\PlatformBillingController::class)->chargeSchool($record))
                    ->visible(fn (PlatformSubscription $record) => $record->status !== 'cancelled'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // ─── Pages ────────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatformSubscriptions::route('/'),
            'create' => Pages\CreatePlatformSubscription::route('/create'),
            'edit' => Pages\EditPlatformSubscription::route('/{record}/edit'),
        ];
    }
}
