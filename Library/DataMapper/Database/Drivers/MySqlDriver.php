<?php

namespace Library\DataMapper\Database\Drivers;

use Library\DataMapper\Mapping\Column;
use Library\DataMapper\Mapping\Metadata;
use PDO;

class MySqlDriver
{
    const NAME = 'mysql';

    protected $dao;

    public function __construct($config)
    {
        $this->dao = new PDO('mysql:host='.$config['host'].';dbname='.$config['database'],
            $config['username'],
            $config['password']);

        $this->dao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function name()
    {
        return self::NAME;
    }

    public function execute($str, $structureType = 'ASSOC')
    {
        $stmt = $this->dao->prepare($str);
        $stmt->execute();

        switch ($structureType)
        {
            case 'ASSOC':
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            case 'SINGLE':
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }

    public function beginTransaction()
    {
        $this->dao->beginTransaction();
    }

    public function rollBack()
    {
        $this->dao->rollBack();
    }

    public function commit()
    {
        $this->dao->commit();
    }

    public function buildWheres(array $wheres)
    {
        if (sizeof($wheres) == 0)
        {
            return '';
        }

        $str = 'WHERE';

        for ($i = 0; $i < sizeof($wheres); $i++)
        {
            if ($i > 0)
            {
                $str .= ' '.$wheres[$i]['link'];
            }

            $var = $this->parseWhereVar($wheres[$i]['var']);

            $str .= ' '.$var;
            $str .= ' '.$wheres[$i]['operator'];

            $value = $wheres[$i]['value'];
            if (trim($wheres[$i]['operator']) != 'in')
            {
                if (substr($value, 0, 1) != '\'' && substr($value, -1) != '\'')
                {
                    $value = '\''.$value.'\'';
                }
            }

            $str .= ' '.$value;
        }

        return $str;
    }

    private function parseWhereVar($var)
    {
        $segments = explode(' ', $var);
        if (sizeof($segments) > 1)
        {
            return 'CONCAT('.$segments[0].', \' \', '.$segments[1].')';
        }

        $segments = explode('%', $var);
        if (sizeof($segments) > 1)
        {
            return 'CONCAT('.$segments[0].', \'%\', '.$segments[1].')';
        }

        return $var;
    }

    public function buildSorts(array $rules)
    {
        if (sizeof($rules) == 0)
        {
            return '';
        }

        $str = 'ORDER BY ';

        $segments = [];
        foreach ($rules as $field => $ascending)
        {
            $segments[] = $field.' '.($ascending ? 'ASC' : 'DESC');
        }

        $str .= implode(', ', $segments);

        return $str;
    }

    public function buildLimit($number)
    {
        if (is_null($number))
        {
            return '';
        }

        return ' LIMIT '.$number;
    }

    public function insert($str, $data)
    {
        $stmt = $this->dao->prepare($str);
        $i = 1;
        foreach ($data as $value)
        {
            if (is_bool($value))
            {
                $stmt->bindValue($i, $value, PDO::PARAM_BOOL);
            }
            else
            {
                $stmt->bindValue($i, $value);
            }
            $i++;
        }
        $stmt->execute();

        return $this->dao->lastInsertId();
    }

    public function insertMany($str, $dataSet)
    {
        $stmt = $this->dao->prepare($str);
        $i = 1;
        foreach ($dataSet as $data)
        {
            foreach ($data as $value)
            {
                $stmt->bindValue($i, $value);
                $i++;
            }
        }
        $stmt->execute();

        $firstId = $this->dao->lastInsertId();
        $insertIds = [];
        $i = 0;
        foreach ($dataSet as $oid => $data)
        {
            $insertIds[$oid] = $firstId + $i;
            $i++;
        }

        return $insertIds;
    }

    public function lastInsertId()
    {
        return $this->dao->lastInsertId();
    }

    public function update($str, $data)
    {
        $stmt = $this->dao->prepare($str);
        $stmt->execute($data);
    }

    public function updateMany($str)
    {
        $this->dao->exec($str);
    }

    public function delete($str)
    {
        return $this->dao->exec($str);
    }
 
    public function createTable(Metadata $metadata)
    {
        if ($this->tableExists($metadata->table()))
        {
            return false;
        }

        $str = 'CREATE TABLE IF NOT EXISTS '.$metadata->table();

        $primaryKeyStr = '';
        $columnsStr = [];
        foreach ($metadata->columns() as $column)
        {
            $column->isPrimaryKey()
                ? $primaryKeyStr = $this->getColumnBodyString($column)
                : $columnsStr[] = $this->getColumnBodyString($column);
        }

        $str .= ' (';
        $str .= $primaryKeyStr.',';
        $str .= ' '.implode(', ', $columnsStr);

        foreach ($metadata->columns() as $column)
        {
            if ($column->isUnique())
            {
                $str .= ', '.$this->getUniqueString($column);
            }
        }

        $str .= ')';

        $this->dao->exec($str);

        return true;
    }

    protected function getColumnBodyString(Column $column)
    {
        if ($column->isPrimaryKey())
        {
            $primaryKeyStr = $column->name();
            $primaryKeyStr .= ' '.$this->getColumnTypeString($column->type());
            $primaryKeyStr .= '('.$column->size().')';
            $primaryKeyStr .= ' UNSIGNED AUTO_INCREMENT PRIMARY KEY';

            return $primaryKeyStr;
        }

        if ($column->isJson())
        {
            return $column->name().' TEXT';
        }

        $columnStr = $column->name();
        $columnStr .= ' '.$this->getColumnTypeString($column->type());
        if ($column->type() != 'datetime' && $column->type() != 'text')
        {
            if ($column->size() > 0)
            {
                $columnStr .= '('.$column->size();
                if ($column->type() == 'decimal')
                    $columnStr .= ', '.$column->precision();
                $columnStr .= ')';
            }
        }
        if (!$column->isNullable())
            $columnStr .= ' NOT NULL';
        if ($column->isDefault())
        {
            $columnStr .= ' DEFAULT';
            if (is_string($column->defaultValue()))
            {
                $columnStr .= ' \''.$column->defaultValue().'\'';
            }
            else if (is_bool($column->defaultValue()))
            {
                $columnStr .= ' '.($column->defaultValue() ? 1 : 0);
            }
            else
            {
                $columnStr .= ' '.$column->defaultValue();
            }
        }

        return $columnStr;
    }

    protected function getUniqueString(Column $column)
    {
        return 'UNIQUE ('.$column->name().')';
    }

    public function dropTable($table)
    {
        if ($this->tableExists($table))
        {
            $this->dao->exec('DROP TABLE '.$table);
        }
    }

    public function addColumnTo($table, Column $column)
    {
        $str = 'ALTER TABLE '.$table.' ADD ';

        $str .= $this->getColumnBodyString($column);

        if ($column->isUnique())
        {
            $str .= ', '.$this->getUniqueString($column);
        }

        $this->dao->exec($str);
    }

    public function dropColumnFrom($table, $column)
    {
        $this->dao->exec('ALTER TABLE '.$table.' DROP '.$column);
    }

    /**
     * Reads the current database structure
     * and constructs an array with all tables and
     * columns usable by the schema tool.
     *
     * @return array
     */
    public function describeSchema()
    {
        $results = [];

        $query = $this->dao->query('show tables');
        $tables = $query->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table)
        {
            $query = $this->dao->query('show columns from '.$table);
            $columns = $query->fetchAll(PDO::FETCH_COLUMN);

            foreach ($columns as $column)
            {
                $query = $this->dao->query('select '.$column.' from '.$table);
                $meta = $query->getColumnMeta(0);

                $isNullable = true;
                foreach ($meta['flags'] as $flag)
                {
                    if ($flag == 'not_null')
                    {
                        $isNullable = false;
                    }
                }

                $results[$table][$column] = [
                    'type' => $this->translatePdoType($meta['native_type']),
                    'size' => $meta['len'],
                    'precision' => $meta['precision'],
                    'isNullable' => $isNullable
                ];
            }
        }

        return $results;
    }

    /**
     * Takes the PDO type name and
     * translates it into one that datamapper
     * can understand.
     *
     * @param string $pdoType
     * @return string
     */
    public function translatePdoType($pdoType)
    {
        switch ($pdoType)
        {
            case 'LONG':
                return 'integer';
            case 'VAR_STRING':
                return 'string';
            case 'DATETIME':
                return 'datetime';
            case 'NEWDECIMAL':
                return 'decimal';
            case 'TINY':
                return 'boolean';
            case 'BLOB':
                return 'text';
        }
    }

    protected function getColumnTypeString($type)
    {
        switch ($type)
        {
            case 'integer':
                return 'INT';
            case 'decimal':
                return 'DECIMAL';
            case 'string':
                return 'VARCHAR';
            case 'text':
                return 'TEXT';
            case 'boolean':
                return 'TINYINT';
            case 'datetime':
                return 'DATETIME';
            default:
                throw new RuntimeException('Unknown column type: '.$type);
                break;
        }
    }

    protected function tableExists($table)
    {
        $results = $this->dao->query("SHOW TABLES LIKE '$table'");

        if (!$results)
        {
            return false;
        }

        if ($results->rowCount() > 0)
        {
            return true;
        }

        return false;
    }
}