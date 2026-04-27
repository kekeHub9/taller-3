<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class DatabaseConnection {
    private static $instance = null;
    private function __construct() {}
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = DB::connection();
        }
        return self::$instance;
    }
    public function test() {
        return " Singleton funcionando - BioManage Sys";
    }
}