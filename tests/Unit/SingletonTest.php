<?php
// tests/Unit/SingletonTest.php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DatabaseConnection;

class SingletonTest extends TestCase
{
    /** @test */
    public function test_singleton_retorna_misma_instancia()
    {
    
        $instance1 = DatabaseConnection::getInstance();
        $instance2 = DatabaseConnection::getInstance();
        
        // Assert
        $this->assertSame($instance1, $instance2, 
            '✅ Singleton debe retornar la misma instancia');
    }
    
    /** @test */
    public function test_singleton_conexion_funciona()
    {
        
        $singleton = DatabaseConnection::getInstance();
        $estado = $singleton->checkConnection();
        
        // Assert
        $this->assertEquals('connected', $estado['status']);
        $this->assertArrayHasKey('driver', $estado);
        $this->assertArrayHasKey('database', $estado);
    }
    
    
    public function test_singleton_metodo_test()
    {
        
        $singleton = DatabaseConnection::getInstance();
        $resultado = $singleton->testSingleton();
        
        // Assert
        $this->assertStringContainsString('✅ Singleton funcionando', $resultado);
    }
}