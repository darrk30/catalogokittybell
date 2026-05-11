<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagen_path')
                    ->label('Imagen')
                    ->disk('public')
                    ->square()
                    ->action(
                        Action::make('ver_imagen')
                            ->modalHeading('Vista previa de la imagen')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Cerrar')
                            ->modalContent(function ($record) {
                                $url = Storage::disk('public')->url($record->imagen_path);
                                return new HtmlString('
                                    <div class="flex justify-center">
                                        <img src="' . $url . '" class="w-full max-w-lg rounded-lg shadow-lg object-contain" />
                                    </div>
                                ');
                            }),
                    ),
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('precio')
                    ->numeric()
                    ->prefix('S/ ')
                    ->sortable(),

                TextColumn::make('descuento')
                    ->numeric()
                    ->suffix(' %')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('stock_calculado') // Usamos el nombre del Accessor que creamos en el modelo
                    ->label('Stock Total')
                    ->numeric(decimalPlaces: 2)
                    ->badge() // Opcional: Se ve muy bien como etiqueta
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger'),
                ToggleColumn::make('estado')
                    ->label('Estado')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('estado', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
