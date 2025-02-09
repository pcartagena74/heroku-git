<?php

namespace App\Traits;

trait InsertOnDuplicateKey
{
    /**
     * Insert using mysql ON DUPLICATE KEY UPDATE.
     *
     * @link http://dev.mysql.com/doc/refman/5.7/en/insert-on-duplicate.html
     *
     * Example:  $data = [
     *     ['id' => 1, 'name' => 'John'],
     *     ['id' => 2, 'name' => 'Mike'],
     * ];
     *
     * @param  array  $data  is an array of array.
     * @param  array  $updateColumns  NULL or [] means update all columns
     * @return int 0 if row is not changed, 1 if row is inserted, 2 if row is updated
     */
    public static function insertOnDuplicateKey(array $data, ?array $updateColumns = null): int
    {
        if (empty($data)) {
            return false;
        }

        // Case where $data is not an array of arrays.
        if (! isset($data[0])) {
            $data = [$data];
        }

        $sql = static::buildInsertOnDuplicateSql($data, $updateColumns);

        $data = static::inLineArray($data);

        return self::getModelConnectionName()->affectingStatement($sql, $data);
    }

    /**
     * Insert using mysql INSERT IGNORE INTO.
     *
     *
     * @return int 0 if row is ignored, 1 if row is inserted
     */
    public static function insertIgnore(array $data): int
    {
        if (empty($data)) {
            return false;
        }

        // Case where $data is not an array of arrays.
        if (! isset($data[0])) {
            $data = [$data];
        }

        $sql = static::buildInsertIgnoreSql($data);

        $data = static::inLineArray($data);

        return self::getModelConnectionName()->affectingStatement($sql, $data);
    }

    /**
     * Insert using mysql REPLACE INTO.
     *
     *
     * @return int 1 if row is inserted without replacements, greater than 1 if rows were replaced
     */
    public static function replace(array $data): int
    {
        if (empty($data)) {
            return false;
        }

        // Case where $data is not an array of arrays.
        if (! isset($data[0])) {
            $data = [$data];
        }

        $sql = static::buildReplaceSql($data);

        $data = static::inLineArray($data);

        return self::getModelConnectionName()->affectingStatement($sql, $data);
    }

    /**
     * Static function for getting table name.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        $class = get_called_class();

        return (new $class)->getTable();
    }

    /**
     * Static function for getting connection name
     *
     * @return string
     */
    public static function getModelConnectionName(): string
    {
        $class = get_called_class();

        return (new $class)->getConnection();
    }

    /**
     * Get the table prefix.
     *
     * @return string
     */
    public static function getTablePrefix(): string
    {
        return self::getModelConnectionName()->getTablePrefix();
    }

    /**
     * Static function for getting the primary key.
     *
     * @return string
     */
    public static function getPrimaryKey(): string
    {
        $class = get_called_class();

        return (new $class)->getKeyName();
    }

    /**
     * Build the question mark placeholder.  Helper function for insertOnDuplicateKeyUpdate().
     * Helper function for insertOnDuplicateKeyUpdate().
     *
     *
     * @return string
     */
    protected static function buildQuestionMarks($data): string
    {
        $row = self::getFirstRow($data);
        $questionMarks = array_fill(0, count($row), '?');

        $line = '('.implode(',', $questionMarks).')';
        $lines = array_fill(0, count($data), $line);

        return implode(', ', $lines);
    }

    /**
     * Get the first row of the $data array.
     *
     *
     * @return mixed
     */
    protected static function getFirstRow(array $data)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Empty data.');
        }

        [$first] = $data;

        if (! is_array($first)) {
            throw new \InvalidArgumentException('$data is not an array of array.');
        }

        return $first;
    }

    /**
     * Build a value list.
     *
     *
     * @return string
     */
    protected static function getColumnList(array $first): string
    {
        if (empty($first)) {
            throw new \InvalidArgumentException('Empty array.');
        }

        return '`'.implode('`,`', array_keys($first)).'`';
    }

    /**
     * Build a value list.
     *
     *
     * @return string
     */
    protected static function buildValuesList(array $updatedColumns): string
    {
        $out = [];

        foreach ($updatedColumns as $key => $value) {
            if (is_numeric($key)) {
                $out[] = sprintf('`%s` = VALUES(`%s`)', $value, $value);
            } else {
                $out[] = sprintf('%s = %s', $key, $value);
            }
        }

        return implode(', ', $out);
    }

    /**
     * Inline a multiple dimensions array.
     *
     *
     * @return array
     */
    protected static function inLineArray(array $data): array
    {
        return call_user_func_array('array_merge', array_map('array_values', $data));
    }

    /**
     * Build the INSERT ON DUPLICATE KEY sql statement.
     *
     *
     * @return string
     */
    protected static function buildInsertOnDuplicateSql(array $data, ?array $updateColumns = null): string
    {
        $first = static::getFirstRow($data);

        $sql = 'INSERT INTO `'.static::getTablePrefix().static::getTableName().'`('.static::getColumnList($first).') VALUES'.PHP_EOL;
        $sql .= static::buildQuestionMarks($data).PHP_EOL;
        $sql .= 'ON DUPLICATE KEY UPDATE ';

        if (empty($updateColumns)) {
            $sql .= static::buildValuesList(array_keys($first));
        } else {
            $sql .= static::buildValuesList($updateColumns);
        }

        return $sql;
    }

    /**
     * Build the INSERT IGNORE sql statement.
     *
     *
     * @return string
     */
    protected static function buildInsertIgnoreSql(array $data): string
    {
        $first = static::getFirstRow($data);

        $sql = 'INSERT IGNORE INTO `'.static::getTablePrefix().static::getTableName().'`('.static::getColumnList($first).') VALUES'.PHP_EOL;
        $sql .= static::buildQuestionMarks($data);

        return $sql;
    }

    /**
     * Build REPLACE sql statement.
     *
     *
     * @return string
     */
    protected static function buildReplaceSql(array $data): string
    {
        $first = static::getFirstRow($data);

        $sql = 'REPLACE INTO `'.static::getTablePrefix().static::getTableName().'`('.static::getColumnList($first).') VALUES'.PHP_EOL;
        $sql .= static::buildQuestionMarks($data);

        return $sql;
    }
}
