<?php

namespace DJEM\Editor\Controls\Traits;

use DJEM\Editor\Controls;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Collection;
use BadMethodCallException;
use ReflectionClass;

trait Editor
{
    private $root = null;
    protected $model = null;

    public function create(Controls\Item $item = null)
    {
        $this->root = $item;

        return $this;
    }

    public function loadModel($model = null)
    {
        $this->model = $model;

        return $this;
    }

    public function model()
    {
        if (! $this->model) {
            $this->loadModel();
        }

        return $this->model;
    }

    public static function createControl($name, $args)
    {
        $name = '\\DJEM\\Editor\\Controls\\'.$name;
        if (class_exists($name)) {
            $object = new ReflectionClass($name);

            return $object->newInstanceArgs($args);
        }

        throw new BadMethodCallException('Can\'t create class '.$name);
    }

    public function __call($name, $args)
    {
        if (! preg_match('/^create/', $name)) {
            throw new BadMethodCallException('Call to undefined method '.get_class($this).'::'.$name);
        }

        $className = preg_replace('/^create/', '', $name);

        return $this->create(self::createControl($className, $args))->root;
    }

    public function getView($item = null)
    {
        if (! $item) {
            $item = $this->root;
        }

        $viewItem = (object) [];
        if ($item) {
            foreach ($item->getProperties($this->model()) as $key => $value) {
                $viewItem->{$key} = $value;
            }

            $bind = $item->getBind();
            if ($bind) {
                $viewItem->bind = $bind;
            }

            if ($item->getItems()) {
                $viewItem->items = [];
                foreach ($item->getItems() as $item) {
                    $viewItem->items[] = $this->getView($item);
                }
            }
        }

        return $viewItem;
    }

    public function getControls($item = null)
    {
        $controls = collect([]);

        if (! $item) {
            $item = $this->root;
        }

        if (is_subclass_of($item, Controls\Control::class)) {
            $controls->put($item->getName(), $item);
        }

        if ($item && $item->getItems()) {
            foreach ($item->getItems() as $item) {
                $controls = $controls->merge($this->getControls($item));
            }
        }

        return $controls;
    }

    public function isRelation($field)
    {
        return method_exists($this->model(), $field);
    }

    public function getRelation($field)
    {
        return $this->model()->{$field}();
    }

    public function prepareValues($controls)
    {
        $getField = (object) [
            'field' => function ($field) {
                return $this->input->get($field);
            },
            'relation' => function ($field) {
                return $this->isRelation($field) ? $this->getRelation($field) : null;
            },
            'model' => function () {
                return $this->model();
            },
        ];

        foreach ($controls as $field => $item) {
            $item->initControl($controls);
            $value = $this->input->get($field);

            $relation = null;

            if ($this->isRelation($field)) {
                $relation = $this->getRelation($field);
            }

            $item->prepareUserValue($value, $getField, $relation);
        }

        return $this;
    }

    public function putFillableData(Collection $controls)
    {
        $fillable = $this->model()->getFillable();
        $controls->each(function (Controls\Control $item, $field) use ($fillable) {
            if ($this->isRelation($field)) {
                if (get_class($this->getRelation($field)) == Relations\BelongsTo::class) {
                    $this->addSingleRelation($item, $field);
                }
            } elseif (in_array($field, $fillable)) {
                $this->model()->{$field} = $item->getUserValue();
            }
        });

        return $this;
    }

    private function putRelatedData(Collection $controls)
    {
        $controls->each(function (Controls\Control $item, $field) {
            if ($this->isRelation($field)) {
                switch (get_class($this->getRelation($field))) {
                    case Relations\BelongsToMany::class:
                    case Relations\MorphToMany::class:
                        $this->addMultipleRelation($item, $field);
                        break;

                    case Relations\HasMany::class:
                        $this->addReversedSingleRelation($item, $field);
                        break;

                    default:
                        // неизвестный relation
                        throw new \Exception('Unknown relation');
                        break;
                }
            }
            $item->putRelatedData($this->model());
        });

        return $this;
    }

    private function addSingleRelation(Controls\Item $item, $field)
    {
        $relation = $this->getRelation($field);

        $value = $item->getUserValue();

        if (empty($value)) {
            $relation->dissociate();
        } else {
            $relation->associate($value);
        }
    }

    private function addMultipleRelation(Controls\Item $item, $field)
    {
        $values = $item->getUserValue();
        $relation = $this->getRelation($field);
        $relation->detach();

        if (! empty($values)) {
            foreach ($values as $value) {
                $relation->attach($value);
            }
        }
    }

    private function addReversedSingleRelation(Controls\Item $item, $field)
    {
        $values = $item->getUserValue();
        $relation = $this->getRelation($field);
        $relation->delete();

        if (! empty($values)) {
            foreach ($values as $value) {
                $relation->save($value);
            }
        }
    }

    public function putData($model)
    {
        $this->loadModel($model);
        $controls = $this->getControls();

        $this->prepareValues($controls)
            ->putFillableData($controls)
            ->putRelatedData($this->getControls());

        return $this;
    }
}
