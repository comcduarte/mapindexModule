<?php 
namespace Mapindex\Form;

use Mapindex\Model\MapindexModel;
use Midnet\Form\Element\DatabaseSelectObject;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Form\Element\Date;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Text;

class MapindexForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function initialize()
    {
        parent::initialize();
        
        $this->add([
            'name' => 'MAP',
            'type' => Text::class,
            'attributes' => [
                'id' => 'MAP',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Map Index',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'OWNER',
            'type' => DatabaseSelectObject::class,
            'attributes' => [
                'id' => 'OWNER',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Primary Owner',
                'database_adapter' => $this->adapter,
                'database_table' => 'owners',
                'database_id_column' => 'UUID',
                'database_value_column' => 'NAME',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'NUMBER',
            'type' => Text::class,
            'attributes' => [
                'id' => 'NUMBER',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'House Number',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'STREET',
            'type' => Text::class,
            'attributes' => [
                'id' => 'STREET',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Street Name',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'SEC_STREET',
            'type' => Text::class,
            'attributes' => [
                'id' => 'SEC_STREET',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Secondary Street',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'IMAGE_LOC',
            'type' => Hidden::class,
            'attributes' => [
                'id' => 'IMAGE_LOC',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Image Location',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'DATE_DRAWN',
            'type' => Date::class,
            'attributes' => [
                'id' => 'DATE_DRAWN',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Date Drawn',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'DATE_FILED',
            'type' => Date::class,
            'attributes' => [
                'id' => 'DATE_FILED',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Date Field',
            ],
        ],['priority' => 100]);
        
        
        $this->get('STATUS')->setOptions([
            'value_options' => [
                MapindexModel::ACTIVE_STATUS => 'Active',
                MapindexModel::INACTIVE_STATUS => 'Inactive',
            ],
        ]);
    }
}