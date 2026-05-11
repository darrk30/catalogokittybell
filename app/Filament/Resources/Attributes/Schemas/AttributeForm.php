<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Atributo')
                    ->description('Define el nombre del atributo (ej: Talla) y sus opciones (ej: S, M, L).')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del Atributo')
                            ->placeholder('Ej: Color, Talla, Tela')
                            ->required()
                            ->maxLength(255),

                        Repeater::make('values')
                            ->relationship('values')
                            ->schema([
                                // 1. Selector de tipo
                                Select::make('tipo')
                                    ->label('Tipo de entrada')
                                    ->options([
                                        'texto' => 'Texto',
                                        'color' => 'Color',
                                    ])
                                    ->live()
                                    // MAGIA AQUÍ: Inferir el tipo al cargar los datos (Editar)
                                    ->afterStateHydrated(function (callable $set, ?string $state, ?\Illuminate\Database\Eloquent\Model $record) {
                                        if (!$state) {
                                            if ($record && $record->valor && str_starts_with($record->valor, '#')) {
                                                $set('tipo', 'color');
                                            } else {
                                                $set('tipo', 'texto');
                                            }
                                        }
                                    })
                                    ->columnSpan(1),

                                TextInput::make('nombre')
                                    ->label('Nombre (Ej: Rojo, XL)')
                                    ->required()
                                    ->columnSpan(1),

                                ColorPicker::make('valor')
                                    ->label('Selecciona el Color')
                                    ->hidden(fn(Get $get) => $get('tipo') !== 'color')
                                    ->required(fn(Get $get) => $get('tipo') === 'color')
                                    ->columnSpan(1),

                                TextInput::make('valor')
                                    ->label('Valor de Texto')
                                    ->placeholder('Ej: Lino, S, M')
                                    ->hidden(fn(Get $get) => $get('tipo') !== 'texto')
                                    ->required(fn(Get $get) => $get('tipo') === 'texto')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->itemLabel(fn(array $state): ?string => $state['nombre'] ?? null)
                            ->collapsible()
                            ->addActionLabel('Agregar Valor')
                    ])->columnSpanFull(),
            ]);
    }
}
