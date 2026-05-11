<?php

namespace App\Filament\Resources\ProductOptions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // 1. FILTRAR: Confiamos en que Livewire ya tiene los IDs validados por el mount()
            ->modifyQueryUsing(function (Builder $query, $livewire) {
                $query->where('product_id', $livewire->product_id)
                    ->where('attribute_id', $livewire->attribute_id)
                    ->where('estado', true);
            })
            ->columns([
                // Solo mostramos el valor, ya que el atributo está en el título de la página
                TextColumn::make('valor.nombre')
                    ->label('Valor (Variante)'),

                TextColumn::make('precio_extra')
                    ->label('Precio Extra')
                    ->money('PEN'),

                TextColumn::make('stock')
                    ->label('Stock Adicional'),

                // ToggleColumn::make('estado')
                //     ->label('Activo'),
            ])
            ->recordActions([
                // Abre el modal flotante
                EditAction::make()->iconButton()
            ]);
    }
}
