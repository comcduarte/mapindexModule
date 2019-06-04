<?php 
namespace Mapindex\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;

class SearchForm extends Form
{
    public function initialize()
    {
        $this->add([
            'name' => 'SEARCH',
            'type' => Text::class,
            'attributes' => [
                'id' => 'SEARCH',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Find',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'BY',
            'type' => Select::class,
            'attributes' => [
                'id' => 'BY',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Search By',
                'value_options' => [
                    "Street" => "Street",
                    "Index" => "Index",
                ],
            ],
        ],['priority' => 100]);
        
        $this->add(new Csrf('SECURITY'),['priority' => 0]);
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Search',
                'class' => 'btn btn-primary form-control mt-4',
                'id' => 'SUBMIT',
            ],
        ],['priority' => 0]);
    }
}