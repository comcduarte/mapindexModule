<?php 
namespace Mapindex\Form;

use Zend\Form\Form;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\File;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;

class UploadFileForm extends Form
{
    public function initialize()
    {
        $this->add([
            'name' => 'FILE',
            'type' => File::class,
            'attributes' => [
                'id' => 'FILE',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Upload File',
            ],
        ]);
        
        $this->add([
            'name' => 'BREAK',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Break',
                'checked_value' => 'yes',
                'unchecked_value' => 'no',
            ],
            'attributes' => [
                'value' => 'yes',
                'class' => 'form-control mt-4',
            ],
        ]);
        
        $this->add([
            'name' => 'BREAK_ON',
            'type' => Text::class,
            'options' => [
                'label' => 'Break On',
            ],
            'attributes' => [
                'class' => 'form-control',
                'value' => 20,
            ],
        ]);
        
        
        $this->add(new Csrf('SECURITY'));
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary form-control',
                'id' => 'SUBMIT',
            ],
        ]);
    }
    
    public function addInputFilter()
    {
        $inputFilter = new InputFilter();
        
        $fileInput = new FileInput('FILE');
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => './data/',
                'randomize' => true,
            )
            );
        $inputFilter->add($fileInput);
        
        $this->setInputFilter($inputFilter);
    }
}