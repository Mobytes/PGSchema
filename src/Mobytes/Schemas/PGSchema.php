<?php
/*
 *
 *  * Copyright (C) 2015 eveR Vásquez.
 *  *
 *  * Licensed under the Apache License, Version 2.0 (the "License");
 *  * you may not use this file except in compliance with the License.
 *  * You may obtain a copy of the License at
 *  *
 *  *      http://www.apache.org/licenses/LICENSE-2.0
 *  *
 *  * Unless required by applicable law or agreed to in writing, software
 *  * distributed under the License is distributed on an "AS IS" BASIS,
 *  * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  * See the License for the specific language governing permissions and
 *  * limitations under the License.
 *
 */

namespace Mobytes\Schemas;

use DB;
use Artisan;
use Schema;

/**
 * Class PGSchema
 * @package Mobytes\Schemas
 */
class PGSchema
{

    /**
     * List all the tables for a schema
     *
     * @param $schemaName
     * @return array|static[]
     */
    protected function listTables($schemaName)
    {
        $tables = DB::table('information_schema.tables')
            ->select('table_name')
            ->where('table_schema', '=', $schemaName)
            ->get();
        return $tables;
    }

    /**
     * Check to see if a table exists within a schema
     *
     * @param $schemaName
     * @param $tableName
     * @return bool
     */
    protected function tableExists($schemaName, $tableName)
    {
        $tables = $this->listTables($schemaName);
        foreach ($tables as $table) {
            if ($table->table_name === $tableName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check to see if a schema exists
     *
     * @param $schemaName
     * @return bool
     */
    public function schemaExists($schemaName)
    {
        $schema = DB::table('information_schema.schemata')
            ->select('schema_name')
            ->where('schema_name', '=', $schemaName)
            ->count();
        return ($schema > 0);
    }

    /**
     * Create a new schema
     *
     * @param $schemaName
     */
    public function create($schemaName)
    {
        $query = DB::statement('CREATE SCHEMA ' . $schemaName);
    }

    /**
     * Set the search_path to the schema name
     *
     * @param string $schemaName
     */
    public function switchTo($schemaName = 'public')
    {
        if (!is_array($schemaName)) {
            $schemaName = [$schemaName];
        }
        $query = 'SET search_path TO ' . implode(',', $schemaName);
        $result = DB::statement($query);
    }

    /**
     *Drop an existing schema
     *
     * @param $schemaName
     */
    public function drop($schemaName)
    {
        $query = DB::statement('DROP SCHEMA '.$schemaName . ' CASCADE');
    }

    /**
     * Run migrations on a schema
     *
     * @param $schemaName
     * @param array $args
     */
    public function migrate($schemaName, $args = [])
    {
        $this->switchTo($schemaName);
        if (!$this->tableExists($schemaName, 'migrations')) {
            Artisan::call('migrate:install');
        }
        Artisan::call('migrate', $args);
    }

    /**
     * Re-run all the migrations on a schema
     *
     * @param $schemaName
     * @param array $args
     */
    public function migrateRefresh($schemaName, $args = [])
    {
        $this->switchTo($schemaName);
        Artisan::call('migrate:refresh', $args);
    }

    /**
     * Reverse all migrations on a schema
     *
     * @param $schemaName
     * @param array $args
     */
    public function migrateReset($schemaName, $args = [])
    {
        $this->switchTo($schemaName);
        Artisan::call('migrate:reset', $args);
    }

    /**
     * Reverse all migrations on a schema
     *
     * @param $schemaName
     * @param array $args
     */
    public function migrateRollback($schemaName, $args = [])
    {
        $this->switchTo($schemaName);
        Artisan::call('migrate:rollback', $args);
    }

    /**
     * Return the current search path
     *
     * @return mixed
     */
    public function getSearchPath()
    {
        $query = DB::select('show search_path');
        $searchPath = array_pop($query)->search_path;
        return $searchPath;
    }
}