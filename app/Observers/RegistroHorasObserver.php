<?php
namespace App\Observers;
use App\Models\RegistroHoras;
use App\Models\Estudiante;
class RegistroHorasObserver
{
    public function created(RegistroHoras $registroHoras): void
    {
        if ($registroHoras->estado === 'aprobado') {
            $estudiante = Estudiante::find($registroHoras->estudiante_id);
            $estudiante->recalcularHoras();
        }
    }
    public function updated(RegistroHoras $registroHoras): void
    {
        if ($registroHoras->isDirty('estado') || $registroHoras->isDirty('horas_acumuladas')) {
            $estudiante = Estudiante::find($registroHoras->estudiante_id);
            $estudiante->recalcularHoras();
        }
    }
    public function deleted(RegistroHoras $registroHoras): void
    {
        if ($registroHoras->estado === 'aprobado') {
            $estudiante = Estudiante::find($registroHoras->estudiante_id);
            $estudiante->recalcularHoras();
        }
    }
}