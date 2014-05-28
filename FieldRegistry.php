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
     * @param  array      $options
     * @return Field
     * @throws \Exception
     */
    public function resolve($type, Table $table, $options = array())
    {
        if (empty($type)) {
            $type = 'text';
        }
        if (!array_key_exists($type, $this->types)) {
            throw new \Exception(sprintf("Field type '%s' does not exist!", $type));
        }
        $class = $this->types[$type];

        //$path = explode('.', $path);
        //$size = count($path);

        //for ($i = 0; $i < $size; $i++) {
            /**
             * @var AbstractField $field
             */
            //if (isset($path[$i+1])) {
            //    $field = $parent->addJoin($path[$i]);
            //} else {
                $field = new $class($table, $options);
                //$field->setPath($path);
            //}

            //$parent = $field;
        //}

        // return last generated field;
        return $field;
    }

    protected function registerStandardFields()
    {
        $this->register("empty", '\\NeuroSYS\\DoctrineDatatables\\Field\\EmptyField');
        $this->register("text", '\\NeuroSYS\\DoctrineDatatables\\Field\\TextField');
        $this->register("number", '\\NeuroSYS\\DoctrineDatatables\\Field\\NumberField');
        $this->register("choice", '\\NeuroSYS\\DoctrineDatatables\\Field\\ChoiceField');
        $this->register("boolean", '\\NeuroSYS\\DoctrineDatatables\\Field\\BooleanField');
        $this->register("date", '\\NeuroSYS\\DoctrineDatatables\\Field\\DateField');
        $this->register("entity", '\\NeuroSYS\\DoctrineDatatables\\Field\\Entity');

        return $this;
    }

}
