<?php 
namespace Mapindex\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;

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