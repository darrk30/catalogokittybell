<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            // DeleteAction::make(),
        ];
    }

    // 1. CARGAR LOS DATOS: Agrupa los registros de la BD para mostrarlos en el Repeater
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $producto = $this->record;

        // 🚩 AQUÍ ESTABA EL ERROR: Solo debemos cargar las opciones ACTIVAS
        $opciones = $producto->productoOpciones()
            ->where('estado', true) // <--- Filtro indispensable
            ->get();

        $agrupados = [];
        foreach ($opciones as $opcion) {
            $attrId = $opcion->attribute_id;
            if (!isset($agrupados[$attrId])) {
                $agrupados[$attrId] = [
                    'attribute_id' => $attrId,
                    'value_ids' => []
                ];
            }
            $agrupados[$attrId]['value_ids'][] = $opcion->value_id;
        }

        $data['opciones_configuradas'] = array_values($agrupados);
        return $data;
    }

    // 2. GUARDAR LOS DATOS: Al editar, borramos las opciones viejas y creamos las nuevas
    protected function afterSave(): void
    {
        $producto = $this->record;
        $opcionesSeleccionadas = $this->data['opciones_configuradas'] ?? [];

        // 1. Creamos una lista de lo que el usuario quiere tener activo AHORA
        $nuevasLlaves = [];
        foreach ($opcionesSeleccionadas as $fila) {
            $attrId = $fila['attribute_id'];
            foreach ($fila['value_ids'] as $valId) {
                $nuevasLlaves[] = "{$attrId}-{$valId}";
            }
        }

        // 2. Revisamos TODAS las variantes que existen en la BD para este producto
        $opcionesEnBD = $producto->productoOpciones()->get();

        foreach ($opcionesEnBD as $opcionBD) {
            $llaveActual = "{$opcionBD->attribute_id}-{$opcionBD->value_id}";

            if (in_array($llaveActual, $nuevasLlaves)) {
                // Si está en el formulario -> Aseguramos que esté Activo
                $opcionBD->update(['estado' => true]);
            } else {
                // Si NO está en el formulario -> Lo desactivamos (pero no lo borramos)
                $opcionBD->update(['estado' => false]);
            }
        }

        // 3. Creamos las que son totalmente nuevas (que no existían ni como inactivas)
        foreach ($opcionesSeleccionadas as $fila) {
            $attrId = $fila['attribute_id'];
            foreach ($fila['value_ids'] as $valId) {
                $producto->productoOpciones()->firstOrCreate(
                    ['attribute_id' => $attrId, 'value_id' => $valId],
                    ['estado' => true, 'precio_extra' => 0, 'stock' => 0]
                );
            }
        }
    }
}
