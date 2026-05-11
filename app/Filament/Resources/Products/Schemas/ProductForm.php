<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\Value;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Configuración del Producto')
                    ->tabs([
                        // Pestaña 1: Información General
                        // En la pestaña 'Información General'
                        Tab::make('Información General')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Grid::make(2)->schema([
                                    FileUpload::make('imagen_path')
                                        ->label('Seleccionar Archivo')
                                        ->image()
                                        ->optimize('webp')
                                        ->directory(function () {
                                            $nombre = Str::slug(Auth::user()->name, '_');
                                            $id = Auth::id();
                                            return "{$nombre}_{$id}/productos";
                                        }),
                                    FileUpload::make('imagen_path_tallas')
                                        ->label('Seleccionar imagen para tallas')
                                        ->image()
                                        ->optimize('webp')
                                        ->directory(function () {
                                            $nombre = Str::slug(Auth::user()->name, '_');
                                            $id = Auth::id();
                                            return "{$nombre}_{$id}/tallas";
                                        }),
                                ]),

                                Grid::make(2)->schema([
                                    TextInput::make('nombre')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                                    TextInput::make('slug')
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->unique(ignoreRecord: true),

                                    Grid::make([
                                        'default' => 1,
                                        'sm' => 1,
                                        'md' => 2,
                                        'lg' => 3,
                                    ])->schema([
                                        TextInput::make('precio')
                                            ->label('Precio Base')
                                            ->numeric()
                                            ->prefix('S/')
                                            ->required()
                                            ->live(onBlur: true) // Escucha los cambios cuando el usuario sale del campo
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                $precio = floatval($state);
                                                $descuento = floatval($get('descuento'));

                                                // Calculamos el precio final
                                                $precioFinal = $precio - ($precio * ($descuento / 100));

                                                // Actualizamos el campo de precio_con_descuento
                                                $set('precio_con_descuento', number_format($precioFinal, 2, '.', ''));
                                            }),

                                        TextInput::make('descuento')
                                            ->label('Descuento')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(0) // Es buena práctica poner 0 por defecto
                                            ->required()
                                            ->live(onBlur: true) // Escucha los cambios
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                $descuento = floatval($state);
                                                $precio = floatval($get('precio'));
                                                $precioFinal = $precio - ($precio * ($descuento / 100));
                                                $set('precio_con_descuento', number_format($precioFinal, 2, '.', ''));
                                            }),

                                        TextInput::make('precio_con_descuento')
                                            ->label('Precio final al cliente')
                                            ->numeric()
                                            ->prefix('S/')
                                            ->disabled()
                                            ->dehydrated() // <--- CAMBIO: Quita el 'false' o déjalo así para que SÍ se envíe a la BD
                                            ->formatStateUsing(function (Get $get) {
                                                $precio = floatval($get('precio'));
                                                $descuento = floatval($get('descuento'));
                                                $precioFinal = $precio - ($precio * ($descuento / 100));

                                                return number_format($precioFinal, 2, '.', '');
                                            }),

                                        TextInput::make('stock')
                                            ->label('Stock')
                                            ->numeric()
                                            ->step(0.01)
                                            ->default(0)
                                            ->disabled(fn(?Product $record) => $record && $record->productoOpciones()->where('estado', true)->sum('stock') > 0)
                                            ->formatStateUsing(function (?Product $record, $state) {
                                                if ($record && $record->productoOpciones()->where('estado', true)->sum('stock') > 0) {
                                                    return $record->stock_calculado;
                                                }
                                                return $state;
                                            })
                                            ->dehydrated(fn(?Product $record) => !$record || $record->productoOpciones()->where('estado', true)->sum('stock') <= 0)
                                            ->helperText(
                                                fn(?Product $record) => ($record && $record->productoOpciones()->where('estado', true)->sum('stock') > 0)
                                                    ? 'Stock en variantes'
                                                    : 'Stock Base'
                                            ),

                                        Select::make('categorie_id')
                                            ->label('Categoría')
                                            ->relationship('categoria', 'nombre')
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        TextInput::make('codigo')
                                            ->label('Codigo')
                                            ->required(),

                                        Toggle::make('estado')
                                            ->label('Producto Visible')
                                            ->default(true)
                                            ->inline(false)
                                            ->required(),

                                    ])->columnSpanFull(),


                                    MarkdownEditor::make('descripcion')
                                        ->columnSpanFull(),
                                ]),
                            ]),

                        // Pestaña 2: Variantes
                        Tab::make('Variantes')
                            ->icon('heroicon-m-swatch')
                            ->schema([
                                Repeater::make('opciones_configuradas')
                                    ->label('Configuración de Atributos')
                                    ->table([
                                        TableColumn::make('Atributo'),
                                        TableColumn::make('Valor'),
                                    ])
                                    ->compact()
                                    ->reorderable(false)
                                    ->schema([
                                        Select::make('attribute_id')
                                            ->label('Atributo')
                                            ->options(Attribute::query()->pluck('nombre', 'id'))
                                            ->native(false)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn(callable $set) => $set('value_ids', []))
                                            ->createOptionForm([
                                                TextInput::make('nombre')
                                                    ->label('Nombre del Atributo (ej. Color, Talla)')
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->createOptionUsing(function (array $data) {
                                                $atributo = Attribute::create([
                                                    'nombre' => $data['nombre'],
                                                    'estado' => true,
                                                    'user_id' => Auth::id(),
                                                ]);
                                                return $atributo->id;
                                            }),

                                        Select::make('value_ids')
                                            ->label('Valores')
                                            ->multiple()
                                            ->native(false)
                                            ->searchable()
                                            ->preload()
                                            ->options(
                                                fn(Get $get) => \App\Models\Value::query()
                                                    ->where('attribute_id', $get('attribute_id'))
                                                    ->where('estado', true)
                                                    ->pluck('nombre', 'id')
                                            )
                                            ->required()
                                            ->disabled(fn(Get $get) => !$get('attribute_id'))
                                            ->createOptionForm([
                                                Select::make('tipo')
                                                    ->label('Tipo de entrada')
                                                    ->options([
                                                        'texto' => 'Texto',
                                                        'color' => 'Color',
                                                    ])
                                                    ->default('texto')
                                                    ->live()
                                                    ->required()
                                                    ->columnSpanFull(),

                                                TextInput::make('nombre')
                                                    ->label('Nombre (Ej: Rojo, XL)')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),

                                                ColorPicker::make('valor_color')
                                                    ->label('Selecciona el Color')
                                                    ->hidden(fn(Get $get) => $get('tipo') !== 'color')
                                                    ->required(fn(Get $get) => $get('tipo') === 'color')
                                                    ->columnSpanFull(),

                                                TextInput::make('valor_texto')
                                                    ->label('Valor de Texto')
                                                    ->placeholder('Ej: Lino, S, M')
                                                    ->hidden(fn(Get $get) => $get('tipo') !== 'texto')
                                                    ->required(fn(Get $get) => $get('tipo') === 'texto')
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),
                                            ])
                                            ->createOptionUsing(function (array $data, Get $get) {
                                                $attributeId = $get('attribute_id');
                                                if (!$attributeId) {
                                                    return null;
                                                }
                                                $valorDefinitivo = $data['tipo'] === 'color'  ? $data['valor_color'] : $data['valor_texto'];
                                                $valor = Value::create([
                                                    'nombre' => $data['nombre'],
                                                    'valor' => $valorDefinitivo,
                                                    'estado' => true,
                                                    'attribute_id' => $attributeId,
                                                ]);

                                                return $valor->id;
                                            }),
                                    ])
                                    ->addActionLabel('Agregar Nuevo Atributo')
                                    ->defaultItems(0)
                                    ->extraItemActions([
                                        Action::make('configurar')
                                            ->label('Configurar')
                                            ->icon('heroicon-m-cog-8-tooth')
                                            ->color('info')
                                            ->tooltip('Configurar')
                                            ->visible(fn(string $operation) => $operation === 'edit')
                                            ->url(function ($record, array $arguments, Repeater $component) {
                                                // FIX: Verificamos que el item exista antes de pedir el estado
                                                if (!isset($arguments['item'])) return null;

                                                try {
                                                    $itemState = $component->getItemState($arguments['item']);
                                                    $attributeId = $itemState['attribute_id'] ?? '';
                                                    return '/admin/product-options?product_id=' . $record->id . '&attribute_id=' . $attributeId;
                                                } catch (\Exception $e) {
                                                    return null;
                                                }
                                            }),
                                    ])
                                    ->itemLabel(function (array $state): ?string {
                                        if (isset($state['attribute_id'])) {
                                            return Attribute::find($state['attribute_id'])?->nombre;
                                        }
                                        return null;
                                    }),
                            ]),

                        Tab::make('Multimedia')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                FileUpload::make('imagenes_extras')
                                    ->label('Galería de Imágenes')
                                    ->multiple()
                                    ->maxFiles(5) // Límite máximo de 5 imágenes
                                    ->image()
                                    ->optimize('webp', 80)
                                    ->directory(function () {
                                        $nombre = Str::slug(Auth::user()->name, '_');
                                        $id = Auth::id();
                                        return "{$nombre}_{$id}/productos";
                                    })
                                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                        return (string) str($file->getClientOriginalName());
                                    })
                                    ->reorderable()
                                    ->panelLayout('grid')
                                    ->afterStateHydrated(function (FileUpload $component, $record) {
                                        if ($record) {
                                            $component->state(
                                                $record->imagenes()
                                                    ->orderBy('orden')
                                                    ->pluck('path')
                                                    ->toArray()
                                            );
                                        }
                                    })
                                    ->dehydrated(false)
                                    ->saveRelationshipsUsing(function ($record, $state) {
                                        // Elimina las imágenes que ya no están presentes en el estado
                                        $record->imagenes()->whereNotIn('path', $state)->delete();

                                        // Actualiza o crea los registros en la tabla polimórfica manteniendo el orden
                                        foreach ($state as $index => $path) {
                                            $record->imagenes()->updateOrCreate(
                                                ['path' => $path],
                                                ['orden' => $index]
                                            );
                                        }
                                    }),
                            ])
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
