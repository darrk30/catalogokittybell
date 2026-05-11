<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $producto = $this->record;
        $opcionesSeleccionadas = $this->data['opciones_configuradas'] ?? [];

        foreach ($opcionesSeleccionadas as $opcion) {
            $attributeId = $opcion['attribute_id'];
            
            foreach ($opcion['value_ids'] as $valueId) {
                $producto->productoOpciones()->create([
                    'attribute_id' => $attributeId,
                    'value_id' => $valueId,
                ]);
            }
        }
    }
}
