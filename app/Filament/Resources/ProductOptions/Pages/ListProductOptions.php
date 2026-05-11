<?php

namespace App\Filament\Resources\ProductOptions\Pages;

use App\Filament\Resources\ProductOptions\ProductOptionResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Attribute;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;

class ListProductOptions extends ListRecords
{
    protected static string $resource = ProductOptionResource::class;

    #[Url]
    public ?string $product_id = null;

    #[Url]
    public ?string $attribute_id = null;

    public function mount(): void
    {
        parent::mount();
        if (empty($this->product_id) || empty($this->attribute_id)) {
            abort(404, 'Página no encontrada o acceso inválido.');
        }
    }

    public function getTitle(): string | Htmlable
    {
        $atributo = Attribute::find($this->attribute_id);
        if ($atributo) {
            return "Opciones de {$atributo->nombre}";
        }
        return 'Opciones de Producto';
    }

    public function getBreadcrumbs(): array
    {
        $producto = Product::find($this->product_id);
        $atributo = Attribute::find($this->attribute_id);
        if (!$producto || !$atributo) {
            return parent::getBreadcrumbs();
        }
        return [
            ProductResource::getUrl('index') => 'Productos',
            ProductResource::getUrl('edit', ['record' => $producto->id]) => $producto->nombre,
            '' => "{$atributo->nombre}",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
