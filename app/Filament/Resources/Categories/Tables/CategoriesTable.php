<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(), // Muestra el slug debajo del nombre

                TextColumn::make('productos_count')
                    ->label('Productos')
                    ->counts('productos') // Asumiendo que tienes la relación 'productos' en el modelo
                    ->badge()
                    ->color('gray'),

                ToggleColumn::make('estado')
                    ->label('Visible')
                    ->onColor('success')
                    ->offColor('danger'),

                TextColumn::make('updated_at')
                    ->label('Última Edición')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true), // Columna oculta opcional
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Filtrar por Estado')
                    ->options([
                        '1' => 'Activas',
                        '0' => 'Inactivas',
                    ]),
            ])
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay categorías creadas')
            ->emptyStateDescription('Comienza creando una nueva categoría para organizar tus productos.')
            ->emptyStateIcon('heroicon-o-tag');
    }
}
