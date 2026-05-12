<?php

namespace App\Filament\Resources\ProductOptions\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Variante')
                    ->description('Configura los valores específicos para esta opción.')
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        // --- Fila 1: Datos numéricos ---
                        TextInput::make('codigo')
                            ->label('Código')
                            ->placeholder('Ej: CAM-ROJO-S')
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        TextInput::make('precio_extra')
                            ->label('Precio Adicional')
                            ->numeric()
                            ->prefix('S/')
                            ->default(0.00)
                            ->required()
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        TextInput::make('stock')
                            ->label('Stock')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->columnSpan(['default' => 12, 'md' => 4]),

                        // Campo de subida: Oculto por defecto
                        FileUpload::make('imagen_path')
                            ->label('Imagen de Variante')
                            ->image()
                            ->optimize('webp', 80)
                             ->directory(function () {
                                $nombre = Str::slug(Auth::user()->name, '_');
                                $id = Auth::id();
                                return "{$nombre}_{$id}/productos";
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}