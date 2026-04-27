<?php
//php artisan test tests/Unit/EquipoTest.php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Equipo;
use App\Models\Mantenimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EquipoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear datos de prueba básicos
        Equipo::create([
            'numero_serie' => 'TEST-001',
            'nombre' => 'Equipo de Prueba 1',
            'tipo' => 'Diagnóstico',
            'marca' => 'General Electric',
            'modelo' => 'Vivid E95',
            'fecha_adquisicion' => '2023-01-01',
            'costo' => 100000.00,
            'departamento' => 'Cardiología',
            'estado' => 'Activo'
        ]);
    }

    public function test_creacion_equipo()
    {
        $data = [
            'numero_serie' => 'TEST-002',
            'nombre' => 'Monitor de Signos Vitales',
            'tipo' => 'Monitor',
            'marca' => 'Philips',
            'modelo' => 'MX450',
            'fecha_adquisicion' => '2022-06-15',
            'costo' => 25000.00,
            'departamento' => 'UCI',
            'estado' => 'Activo'
        ];
        
        $equipo = Equipo::create($data);
        
        $this->assertDatabaseHas('equipos', [
            'numero_serie' => 'TEST-002',
            'nombre' => 'Monitor de Signos Vitales'
        ]);
        
        $this->assertEquals('Activo', $equipo->estado);
        $this->assertEquals('UCI', $equipo->departamento);
    }

    public function test_actualizacion_equipo()
    {
        $equipo = Equipo::where('numero_serie', 'TEST-001')->first();
        
        $equipo->update([
            'estado' => 'Reparación',
            'departamento' => 'Taller'
        ]);
        
        $this->assertDatabaseHas('equipos', [
            'numero_serie' => 'TEST-001',
            'estado' => 'Reparación',
            'departamento' => 'Taller'
        ]);
    }

    public function test_eliminacion_equipo()
    {
        $equipo = Equipo::where('numero_serie', 'TEST-001')->first();
        $equipoId = $equipo->id;
        
        $this->assertDatabaseHas('equipos', ['id' => $equipoId]);
        
        $equipo->delete();
        
        $this->assertDatabaseMissing('equipos', ['id' => $equipoId]);
    }

    public function test_equipos_por_departamento()
    {
        // Crear más equipos en diferentes departamentos
        Equipo::create([
            'numero_serie' => 'TEST-003',
            'nombre' => 'EKG',
            'departamento' => 'Cardiología',
            'estado' => 'Activo'
        ]);
        
        Equipo::create([
            'numero_serie' => 'TEST-004',
            'nombre' => 'Ultrasonido',
            'departamento' => 'Radiología',
            'estado' => 'Activo'
        ]);
        
        $equiposCardiologia = Equipo::where('departamento', 'Cardiología')->get();
        $equiposRadiologia = Equipo::where('departamento', 'Radiología')->get();
        
        $this->assertCount(2, $equiposCardiologia); // TEST-001 y TEST-003
        $this->assertCount(1, $equiposRadiologia); // TEST-004
    }

    public function test_estadisticas_basicas()
    {
        // Crear más equipos en diferentes estados
        Equipo::create([
            'numero_serie' => 'TEST-005',
            'nombre' => 'Equipo Inactivo',
            'estado' => 'Inactivo'
        ]);
        
        Equipo::create([
            'numero_serie' => 'TEST-006',
            'nombre' => 'Equipo en Reparación',
            'estado' => 'Reparación'
        ]);
        
        $activos = Equipo::where('estado', 'Activo')->count();
        $inactivos = Equipo::where('estado', 'Inactivo')->count();
        $reparacion = Equipo::where('estado', 'Reparación')->count();
        
        $this->assertEquals(1, $activos); // TEST-001
        $this->assertEquals(1, $inactivos); // TEST-005
        $this->assertEquals(1, $reparacion); // TEST-006
    }

    public function test_relacion_con_mantenimientos()
    {
        $equipo = Equipo::where('numero_serie', 'TEST-001')->first();
        
        // Crear mantenimiento para el equipo
        Mantenimiento::create([
            'equipo_id' => $equipo->id,
            'tipo' => 'Preventivo',
            'fecha_programada' => now()->addDays(7),
            'estado' => 'Pendiente',
            'tecnico' => 'Juan Pérez',
            'descripcion' => 'Mantenimiento preventivo trimestral'
        ]);
        
        $mantenimientos = $equipo->mantenimientos;
        
        $this->assertCount(1, $mantenimientos);
        $this->assertEquals('Preventivo', $mantenimientos->first()->tipo);
    }
}