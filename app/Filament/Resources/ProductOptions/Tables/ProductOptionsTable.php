<?php

namespace App\Filament\Resources\ProductOptions\Tables;

use App\Models\Attribute;
use App\Models\Exclusion;
use App\Models\Value;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query, $livewire) {
                $query->where('product_id', $livewire->product_id)
                    ->where('attribute_id', $livewire->attribute_id)
                    ->where('estado', true);
            })
            ->columns([
                TextColumn::make('valor.nombre')
                    ->label('Valor (Variante)'),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('precio_extra')
                    ->label('Precio Extra')
                    ->money('PEN'),

                TextColumn::make('stock')
                    ->label('Stock'),

                // Badge con conteo de exclusiones activas
                TextColumn::make('exclusiones_count')
                    ->label('Exclusiones')
                    ->counts('exclusiones')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'gray')
                    ->tooltip('Combinaciones bloqueadas'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalHeading(fn($record) => "Editar variante — {$record->value?->nombre}")
                    ->modalWidth('lg'),

                // ── ACCIÓN EXCLUSIONES ────────────────────────────
                Action::make('excluir')
                    ->icon('heroicon-o-no-symbol')
                    ->iconButton()
                    ->color('warning')
                    ->tooltip('Gestionar exclusiones')

                    // Cargar exclusiones existentes al abrir el modal
                    ->fillForm(function ($record): array {
                        $exclusiones = $record->exclusiones()
                            ->get()
                            ->map(fn($e) => [
                                'attribute_id' => $e->attribute_id,
                                'value_id'   => $e->value_id,
                            ])
                            ->toArray();

                        return ['exclusiones' => $exclusiones];
                    })

                    ->schema([
                        Repeater::make('exclusiones')
                            ->label('Combinaciones bloqueadas')
                            ->helperText(
                                'Cada fila bloquea un valor de un atributo cuando esta opción está seleccionada. '
                                . 'Ejemplo: si esta opción es "Talla M", puedes bloquear "Color → Amarillo".'
                            )
                            ->schema([
                                Select::make('attribute_id')
                                    ->label('Atributo a bloquear')
                                    ->options(
                                        Attribute::where('estado', true)
                                            ->pluck('nombre', 'id')
                                    )
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($set) => $set('value_id', null))
                                    ->placeholder('Selecciona un atributo'),

                                Select::make('value_id')
                                    ->label('Valor a bloquear')
                                    ->required()
                                    ->placeholder('Selecciona un valor')
                                    ->options(function ($get) {
                                        $atributoId = $get('attribute_id');
                                        if (!$atributoId) return [];

                                        return Value::where('attribute_id', $atributoId)
                                            ->where('estado', true)
                                            ->pluck('nombre', 'id');
                                    })
                                    ->disabled(fn($get) => !$get('attribute_id')),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Agregar exclusión')
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->deletable(true),
                    ])

                    ->action(function ($record, array $data): void {
                        // Borrar todas las exclusiones anteriores de esta opción
                        $record->exclusiones()->delete();

                        // Insertar las nuevas evitando duplicados
                        foreach ($data['exclusiones'] ?? [] as $excl) {
                            if (empty($excl['attribute_id']) || empty($excl['value_id'])) {
                                continue;
                            }

                            Exclusion::firstOrCreate([
                                'producto_opciones_id' => $record->id,
                                'attribute_id'         => $excl['attribute_id'],
                                'value_id'           => $excl['value_id'],
                            ]);
                        }
                    })

                    ->modalHeading(fn($record) => "Exclusiones — {$record->value?->nombre}")
                    ->modalDescription(
                        'Define qué valores de otros atributos NO son compatibles con esta opción. '
                        . 'Las exclusiones son bidireccionales: recuerda agregarlas en ambas opciones.'
                    )
                    ->modalSubmitActionLabel('Guardar exclusiones')
                    ->modalWidth('lg'),
            ]);
    }
}