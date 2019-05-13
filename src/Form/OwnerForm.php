<?php 
namespace Mapindex\Form;

use Zend\Form\Element\Text;
use Mapindex\Model\OwnerModel;

class OwnerForm extends AbstractBaseForm
{
    public function initialize()
    {
        parent::initialize();
        
        $this->add([
            'name' => 'NAME',
            'type' => Text::class,
            'attributes' => [
                'id' => 'NAME',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Owner Name',
            ],
        ],['priority' => 100]);
        
        $this->get('STATUS')->setOptions([
            'value_options' => [
                OwnerModel::ACTIVE_STATUS => 'Active',
                OwnerModel::INACTIVE_STATUS => 'Inactive',
            ],
        ]);
    }
}