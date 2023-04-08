<?php

namespace JaxonRailey;

class Vane {

    protected string $file;
    protected array  $data     = [];
    protected array  $original = [];
    protected array  $fields   = [];
    protected bool   $filtered = false;


    /**
     * Set select statement
     *
     * @param mixed $fields (optional)
     *
     * @return self
     */

    public function select(...$fields) :self {

        $this->fields = $fields;

        if (in_array('*', $this->fields)) {
            $this->fields = [];
        }

        return $this;
    }


    /**
     * Get single row by id
     *
     * @param string $id
     *
     * @return array|bool
     */

    public function id(string $id) :array|bool {

        return isset($this->data[$id]) ? $this->data[$id] : false;
    }


    /**
     * Set from statement
     *
     * @param string $file
     *
     * @return self
     */

    public function from(string $file) :self {

        $file = $file . '.json';

        if (is_file($file)) {
            $json = file_get_contents($file);
            $json = empty(trim($json)) ? '[]' : $json;
            $this->data = json_decode($json, true);
            $this->original = $this->data;
        }

        $this->file = $file;

        return $this;
    }


    /**
     * Set where statement
     *
     * @param string $field
     * @param string $condition (optional)
     * @param mixed $value (optional)
     *
     * @return self
     */

     public function where(string $field, string $condition = null, mixed $value = null) :self {

        $this->filtered = true;

        $where = [$field, 'like', ''];

        if ($condition) {
            $where = [$field, '=', $condition];
        }

        if (isset($value)) {
            $where = [$field, $condition, $value];
        }

        $this->data = array_filter($this->data, function ($row) use ($where) {
            [$field, $condition, $value] = $where;

            $item = $row;
            foreach (explode('.', $field) as $key) {
                $item = $item[$key] ?? null;
                if (!isset($item)) {
                    return false;
                }
            }

            return $this->compare($item, $condition, $value);
        });

        return $this;
    }


    /**
     * Set exists statement
     *
     * @param string $field
     * @param bool $positive (optional)
     *
     * @return self
     */

    public function exists(string $field, bool $positive = true) :self {

        $this->filtered = true;

        $where = [$field, $positive];

        $this->data = array_filter($this->data, function ($row) use ($where) {
            [$field, $positive] = $where;
            return $positive ? isset($row[$field]) : !isset($row[$field]);
        });

        return $this;
    }


    /**
     * Set contains statement
     *
     * @param string $field
     * @param mixed $value (optional)
     * @param bool $positive (optional)
     *
     * @return self
     */

    public function contains(string $field, mixed $value = null, bool $positive = true) :self {

        $this->filtered = true;

        $where = [$field, $value, $positive];

        $this->data = array_filter($this->data, function ($row) use ($where) {
            [$field, $value, $positive] = $where;
            return $positive ? in_array($value, $row[$field]) : !in_array($value, $row[$field]);
        });

        return $this;
    }


    /**
     * Set counter statement
     *
     * @param string $field
     * @param string $condition (optional)
     * @param mixed $value (optional)
     *
     * @return self
     */

     public function counter(string $field, string $condition = null, mixed $value = null) :self {

        $this->filtered = true;

        $where = [$field, '=', ''];

        if ($condition) {
            if ($condition == 'like') {
                $condition = '=';
            }

            $where = [$field, '=', $condition];
        }

        if (isset($value)) {
            $where = [$field, $condition, $value];
        }

        $this->data = array_filter($this->data, function ($row) use ($where) {
            [$field, $condition, $value] = $where;

            $item = $row;
            foreach (explode('.', $field) as $key) {
                $item = $item[$key] ?? null;
                if (!isset($item)) {
                    return false;
                }
            }

            if (!is_array($item)) {
                return false;
            }

            return $this->compare(count($item), $condition, $value);
        });

        return $this;
    }


    /**
     * Get all rows of results
     *
     * @return array
     */

    public function rows() :array {

        if (!$this->fields) {
            return $this->data;
        }

        $results = [];
        foreach ($this->fields as $fields) {
            foreach ($this->data as $index => $row) {
                $keys  = explode('.', $fields);
                $value = null;

                foreach ($keys as $key) {
                    if (!isset($row[$key])) {
                        $value = null; break;
                    }
                    $row   = $row[$key];
                    $value = $row;
                }

                if ($value) {
                    $current = &$results[$index];
                    foreach ($keys as $key) {
                        $current = &$current[$key];
                    }
                    $current = $value;
                }
            }
        }

        return $results;
    }


    /**
     * Set save/insert statement, if filtered update, then append new rows
     *
     * @param array|object $data
     *
     * @return bool
     */

    public function save(array|object $data) :bool {

        $changed = false;

        // if is un update
        if ($this->filtered) {
            $changed = true;
            foreach ($this->data as $index => $row) {
                $this->data[$index] = array_merge($row, $data);
            }

            $ids = array_intersect_key($this->original, $this->data);
            $this->original = array_replace($this->original, $ids, $this->data);
        }

        // if is new massive insert
        if (!$this->filtered && is_array($data) && is_array($data[0])) {
            $changed = true;
            foreach ($data as $row) {
                $this->original[uniqid()] = $row;
            }
        }

        // if is single array insert
        if (!$this->filtered && is_array($data) && !is_array($data[0])) {
            $changed = true;
            $this->original[uniqid()] = $data;
        }

        // if is single object insert
        if (!$this->filtered && is_object($data)) {
            $changed = true;
            $this->original[uniqid()] = json_decode(json_encode($data), true);
        }

        if ($changed) {
            $json = json_encode($this->original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($this->file, $json);
        }

        $this->data     = [];
        $this->original = [];
        $this->fields   = [];
        $this->filtered = false;

        return true;
    }


    /**
     * Set delete statement
     *
     * @return bool
     */

    public function delete() :bool {

        if ($this->filtered) {
            $this->original = array_diff_key($this->original, $this->data);
        }

        $json = json_encode($this->original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        file_put_contents($this->file, $json);

        $this->data     = [];
        $this->original = [];
        $this->fields   = [];
        $this->filtered = false;

        return true;
    }


    /**
     * Set truncate statement
     *
     * @return bool
     */

     public function truncate() :bool {

        file_put_contents($this->file, '[]');

        $this->data     = [];
        $this->original = [];
        $this->fields   = [];
        $this->filtered = false;

        return true;
    }


    protected function compare(mixed $value, string $condition, mixed $compare) {

        return match(strtolower($condition)) {
            '='     => $value == $compare,
            '!='    => $value != $compare,
            '>'     => $value  > $compare,
            '>='    => $value >= $compare,
            '<'     => $value  < $compare,
            '<='    => $value <= $compare,
            'like'  => str_contains($value, $compare),
            default => false
        };
    }
}
