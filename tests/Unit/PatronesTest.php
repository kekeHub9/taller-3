<?php

namespace Tests\Unit;
//hacer teste en powershield con php artisan test tests/Unit/PatronesTest.php uwuwu 
use Tests\TestCase;
use App\Services\DatabaseConnection;

class PatronesTest extends TestCase 
{
    public function test_singleton() 
    {
        $instancia1 = DatabaseConnection::getInstance();
        $instancia2 = DatabaseConnection::getInstance();
        
        $this->assertSame($instancia1, $instancia2);
    }
}