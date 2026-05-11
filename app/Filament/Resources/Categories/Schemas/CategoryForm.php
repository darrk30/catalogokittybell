<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Categoría')
                    ->description('Define el nombre y el estado de visibilidad de la categoría.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nombre')
                                ->label('Nombre de la Categoría')
                                ->placeholder('Ej: Pizzas, Bebidas, Postres...')
                                ->required(),

                            Toggle::make('estado')
                                ->label('Categoría Activa')
                                ->helperText('Si se desactiva, los productos de esta categoría no se mostrarán en la tienda.')
                                ->default(true)
                                ->required(),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }
}
