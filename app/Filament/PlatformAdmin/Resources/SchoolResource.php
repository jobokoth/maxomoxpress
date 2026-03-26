<?php

namespace App\Filament\PlatformAdmin\Resources;

use App\Filament\PlatformAdmin\Resources\SchoolResource\Pages;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('School Details')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->required()->maxLength(100)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')->email()->maxLength(255),
                Forms\Components\TextInput::make('phone')->maxLength(30),
                Forms\Components\TextInput::make('city')->maxLength(100),
                Forms\Components\Select::make('country')
                    ->options(['KE' => 'Kenya', 'UG' => 'Uganda', 'TZ' => 'Tanzania', 'RW' => 'Rwanda'])
                    ->default('KE'),
            ])->columns(2),

            Forms\Components\Section::make('Status & Subscription')->schema([
                Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                Forms\Components\Toggle::make('is_trial')->label('On Trial'),
                Forms\Components\DateTimePicker::make('trial_ends_at')->label('Trial Ends'),
                Forms\Components\Select::make('subscription_plan')
                    ->options([
                        'starter' => 'Starter (≤100 students)',
                        'growth' => 'Growth (101–400 students)',
                        'enterprise' => 'Enterprise (401+ students)',
                    ]),
                Forms\Components\DateTimePicker::make('subscription_expires_at')->label('Subscription Expires'),
            ])->columns(2),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('city')
                    ->sortable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Students')
                    ->counts('students')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('is_trial')
                    ->label('Trial')
                    ->formatStateUsing(fn ($state) => $state ? 'Trial' : 'Paid')
                    ->colors([
                        'warning' => true,
                        'success' => false,
                    ]),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial Ends')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->trial_ends_at?->isPast() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('subscription_plan')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'starter' => 'gray',
                        'growth' => 'info',
                        'enterprise' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('onboarding_completed_at')
                    ->label('Onboarded')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\TernaryFilter::make('is_trial')->label('On Trial'),
                Tables\Filters\Filter::make('trial_expired')
                    ->label('Trial Expired')
                    ->query(fn (Builder $query) => $query->where('is_trial', true)->where('trial_ends_at', '<', now())),
                Tables\Filters\Filter::make('onboarded')
                    ->label('Onboarding Complete')
                    ->query(fn (Builder $query) => $query->whereNotNull('onboarding_completed_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSchools::route('/'),
            'view' => Pages\ViewSchool::route('/{record}'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
