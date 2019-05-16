<?php 
namespace Mapindex\Form;

use Midnet\Form\Element\DatabaseSelectObject;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Form\Element\Hidden;

class MapindexAssignOwnerForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function initialize()
    {
        parent::initialize();
        
        $this->add([
            'name' => 'OWNER',
            'type' => DatabaseSelectObject::class,
            'attributes' => [
                'id' => 'OWNER',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Owner',
                'database_adapter' => $this->adapter,
                'database_table' => 'owners',
                'database_id_column' => 'UUID',
                'database_value_column' => 'NAME',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'MAP',
            'type' => Hidden::class,
            'attributes' => [
                'id' => 'MAP',
                'class' => 'form-control',
            ],
        ],['priority' => 0]);
        
        $this->remove('UUID');
        $this->remove('STATUS');
    }
}