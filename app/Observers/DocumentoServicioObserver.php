<?php
namespace App\Observers;
use App\Models\DocumentosServicio;
use App\Models\Solicitud;
class DocumentoServicioObserver
{
    public function updated(DocumentosServicio $documento): void
    {
        if ($documento->isDirty('estado')) {
            $solicitud = Solicitud::find($documento->solicitud_id);
            if ($solicitud) {
                switch ($documento->tipo_documento_id) {
                    case 1:
                        $solicitud->estado_carta_presentacion = $documento->estado;
                        break;
                    case 6:
                        $solicitud->estado_carta_aceptacion = $documento->estado;
                        break;
                    case 4:
                        $solicitud->estado_carta_termino = $documento->estado;
                        break;
                }
                $solicitud->save();
            }
        }
    }
}