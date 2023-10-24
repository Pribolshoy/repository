<?php

namespace pribolshoy\repository\frameworks\yii2\drivers;

class RedisDriver extends BaseCacheDriver
{
    protected string $component = 'redis';

    public function get(string $key, array $params = [])
    {
        // по умолчанию вытаскивается одно значение
        $strategy = $params['strategy'] ?? 'getOne';

        if (!method_exists($this, $strategy)) {
            throw new \RuntimeException("Метод $strategy не существует в " . __CLASS__);
        }
        return $this->{$strategy}($key, $params) ?? [];
    }

    /**
     * Получить все значения их хештаблицы по ключу (key).
     *
     * @param string $key ключ вида somekey, без вложенностей через :
     * @param array $params
     *
     * @return array
     */
    protected function getAllHash(string $key, array $params = [])
    {
        if (!$keys = \Yii::$app->{$this->component}->hkeys($key)) return [];

        foreach ($keys as $field) {
            if ($item = $this->getOneHash($key, $field, $params)) {
                $data[] = $item;
            }
        }

        return $data ?? [];
    }

    /**
     * Получить все значения по ключу (key:*)
     *
     * @param string $key ключ вида key, key:field
     * @param array $params
     *
     * @return array
     */
    protected function getAllRaw(string $key, array $params = [])
    {
        if (substr($key, -1) !== '*') {
            if (substr($key, -1) !== ':') $key .= ':';
            $key .= '*';
        }

        if (!$keys = \Yii::$app->{$this->component}->keys($key)) return [];

        if ($keys) {
            foreach ($keys as $key) {
                if ($item = $this->getOne($key, $params)) {
                    $data[] = $item;
                }
            }
        }

        return $data ?? [];
    }

    /**
     * Получить значение по полному ключу
     *
     * @param string $key somekey or somekey:somefield
     * @param array $params
     *
     * @return array|mixed
     */
    protected function getOne(string $key, array $params = [])
    {
        // если ключ вложенный - перенаправляется в выборку через хештаблицу
        if (preg_match('#:#i', $key)) {
            $key_parts = explode(':', $key);
            $field = array_pop($key_parts);
            $key = implode(':', $key_parts);

            $data = \Yii::$app->{$this->component}->hget($key, $field);
        } else {
            $data = \Yii::$app->{$this->component}->get($key);
        }

        return $data ? unserialize($data) : [];
    }

    /**
     * Получить значение из хеш таблицы по ключу и полю
     *
     * @param string $key
     * @param string $field
     * @param array $params
     *
     * @return array|mixed
     */
    protected function getOneHash(string $key, string $field, array $params = [])
    {
        $data = \Yii::$app->{$this->component}->hget($key, $field);
        return $data ? unserialize($data) : [];
    }

    /**
     * @param string $key
     * @param $value
     * @param int $cache_duration
     * @param array $params
     *
     * @return object
     */
    public function set(string $key, $value, int $cache_duration = 0, array $params = []) :object
    {
        // по умолчанию хеш таблица
        $strategy = $params['strategy'] ?? 'hset';

        if (!method_exists($this, $strategy)) {
            throw new \RuntimeException("Метод $strategy не существует в " . __CLASS__);
        }

        return $this->{$strategy}($key, $value, $cache_duration, $params);
    }

    protected function setex(string $key, $value, int $cache_duration = 0, array $params = []) :object
    {
        \Yii::$app->{$this->component}->setex($key, $cache_duration, serialize($value));
        return $this;
    }

    protected function hset(string $key, $value, int $cache_duration = 0, array $params = []) :object
    {
        // только если есть разделение на двоеточие
        if (preg_match('#:#', $key)) {
            $keyParts = explode(':', $key);
            $count = count($keyParts);
            $field = $keyParts[$count-1];
            unset($keyParts[$count-1]);
            $key = implode(':', $keyParts);

            \Yii::$app->{$this->component}->hset($key, $field, serialize($value));
            \Yii::$app->{$this->component}->expireat($key, time() + $cache_duration);
        } else {
            $this->setex($key, $value, $cache_duration, $params);
        }
        return $this;
    }

    public function delete(string $key, array $params = []) :object
    {
        // по умолчанию хеш таблица
        $strategy = $params['strategy'] ?? 'hdel';

        if (!method_exists($this, $strategy)) {
            throw new \RuntimeException("Метод $strategy не существует в " . __CLASS__);
        }

        return $this->{$strategy}($key, $params);
    }

    protected function del(string $key, array $params = []) :object
    {
        \Yii::$app->{$this->component}->del($key);
        return $this;
    }

    protected function hdel(string $key, array $params = []) :object
    {
        // только если есть разделение на двоеточие
        if (preg_match('#:#', $key)) {
            $keyParts = explode(':', $key);
            $count = count($keyParts);
            $field = $keyParts[$count-1];
            unset($keyParts[$count-1]);
            $key = implode(':', $keyParts);

            \Yii::$app->{$this->component}->hdel($key, $field);
        } else {
            $this->del($key, $params);
        }
        return $this;
    }
}

