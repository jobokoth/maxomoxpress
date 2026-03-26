<?php

namespace App\Filament\PlatformAdmin\Resources;

use App\Filament\PlatformAdmin\Resources\PlatformSettingResource\Pages;
use App\Models\PlatformSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlatformSettingResource extends Resource
{
    protected static ?string $model = PlatformSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Platform Settings';

    protected static ?int $navigationSort = 1;

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true)
                ->helperText('Snake_case identifier, e.g. trial_days'),

            Forms\Components\Select::make('type')
                ->options([
                    'string' => 'String',
                    'integer' => 'Integer',
                    'boolean' => 'Boolean',
                    'json' => 'JSON',
                ])
                ->required()
                ->default('string'),

            Forms\Components\TextInput::make('label')
                ->maxLength(150)
                ->helperText('Human-readable label shown in this table'),

            Forms\Components\Textarea::make('value')
                ->required()
                ->rows(3)
                ->helperText('For boolean use 1/0, for JSON use valid JSON'),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('label')
                    ->searchable(),

                Tables\Columns\TextColumn::make('value')
                    ->limit(60),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'string',
                        'info' => 'integer',
                        'success' => 'boolean',
                        'warning' => 'json',
                    ]),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('key');
    }

    // ─── Pages ────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatformSettings::route('/'),
            'create' => Pages\CreatePlatformSetting::route('/create'),
            'edit' => Pages\EditPlatformSetting::route('/{record}/edit'),
        ];
    }
}
