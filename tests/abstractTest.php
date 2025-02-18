<?php

namespace Mdhesari\LaravelCities\Tests;

use Dotenv\Dotenv;
use Mdhesari\LaravelCities\GeoServiceProvider;

abstract class abstractTest extends \Orchestra\Testbench\TestCase {


    // -----------------------------------------------
    //  Global Setup (Run once)
    // -----------------------------------------------

    public static function setUpBeforeClass(): void 
    {
        parent::setUpBeforeClass();
        
        if (file_exists(__DIR__.'/../.env')) {
            $dotenv = Dotenv::createMutable(__DIR__.'/../');
            $dotenv->load();
        }
    }

    // -----------------------------------------------
    //   Set Laravel App Configuration
    // -----------------------------------------------

    protected function getEnvironmentSetUp($app) {
        $config = $app['config']; 

        $config->set('app.debug', 'true');
        $config->set('database.default', 'testbench');
        $config->set('database.connections.testbench', [
            'driver'    => 'mysql',
            'host'      => getenv('DB_HOST'),
            'username'  => getenv('DB_USER'),
            'password'  => getenv('DB_PASS'),
            'database'  => getenv('DB_DATABASE'),
            'port'      => env('DB_PORT', '3306'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => env('DB_STRICT', false),
            'engine'    => null,
        ]);

        $this->pdo = $app['db']->connection()->getPdo();
    }

    // -----------------------------------------------
    //   add Service Providers & Facades
    // -----------------------------------------------

    protected function getPackageProviders($app) {
        return [
            GeoServiceProvider::class
            // Intervention\Image\ImageServiceProvider::class,
        ];
    }


    protected function getPackageAliases($app) {
        return [
            // 'Image' => Intervention\Image\Facades\Photo::class,
        ];
    }
    
    // -----------------------------------------------
    //  Helpers
    // -----------------------------------------------

    public function reloadModel(&$model){
        $className = get_class($model);
        $model = $className::find($model->id);
        return $model;
    }

    public function sql($sql){
        $result = $this->pdo->query($sql);
        if($result === false)
            throw new Exception("Error in SQL : '$sql'\n".self::$mysqli->error, 1);
            
        return $result->fetch();
    }


    // -----------------------------------------------
    //  Added functionality
    // -----------------------------------------------

    protected function seeInDatabase($table, array $data, $connection = null)
    {

        $count = DB::table($table)->where($data)->count();
        
        $this->assertGreaterThan(0, $count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

    protected function notSeeInDatabase($table, array $data, $connection = null)
    {
        $count = DB::table($table)->where($data)->count();
        
        $this->assertEquals(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

}