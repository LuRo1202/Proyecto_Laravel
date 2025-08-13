<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Models\RegistroHoras;
use App\Models\DocumentosServicio;
use App\Observers\RegistroHorasObserver;
use App\Observers\DocumentoServicioObserver;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        RegistroHoras::observe(RegistroHorasObserver::class);
        DocumentosServicio::observe(DocumentoServicioObserver::class);
    }
}