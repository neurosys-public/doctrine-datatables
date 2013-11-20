<?php
namespace NeuroSYS\DoctrineDatatables;

use NeuroSYS\DoctrineDatatables\Field\Entity;
use NeuroSYS\DoctrineDatatables\Field\AbstractField;

class FieldRegistry
{
    /**
     * @var string[]
     */
    private $types = array();

    public function __construct()
    {
        $this->registerStandardFields();
    }

    /**
     * Register new field type
     *
     * @param $type
     * @param $class
     */
    public function register($type, $class)
    {
        $this->types[$type] = $class;
    }

    /**
     * @param $type
     * @param $name
     * @param array $options
     * @return Field
     * @throws \Exception
     */
    public function resolve($type, $path, Entity $root, $options = array())
    {
        if (!array_key_exists($type, $this->types)) {
            throw new \Exception(sprintf("Field type '%s' does not exist!", $type));
        }
        $class = $this->types[$type];

        $path = explode('.', $path);
        $size = count($path);

        for ($i = 0; $i < $size; $i++) {
            $parent = isset($parent) ? $parent : $root;
            /**
             * @var Field $field
             */
            if (isset($path[$i+1])) {
                $field = $parent->getRelation($path[$i]);
            } else {
                $field = new $class($path[$i], null, $options);
                $field->setPath($path);
            }
            $field->setParent($parent);

            $parent = $field;
        }

        // return last generated field;
        return $field;
    }

    protected function registerStandardFields()
    {
        $this->register("text", '\\NeuroSYS\\DoctrineDatatables\\Field\\TextField');
        $this->register("number", '\\NeuroSYS\\DoctrineDatatables\\Field\\NumberField');
        $this->register("choice", '\\NeuroSYS\\DoctrineDatatables\\Field\\ChoiceField');
        $this->register("boolean", '\\NeuroSYS\\DoctrineDatatables\\Field\\BooleanField');
        $this->register("date", '\\NeuroSYS\\DoctrineDatatables\\Field\\DateField');

        return $this;
    }

} 