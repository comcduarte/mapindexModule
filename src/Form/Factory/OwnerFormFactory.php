<?php 
namespace MapIndex\Form\Factory;

use Interop\Container\ContainerInterface;
use Mapindex\Form\OwnerForm;
use Mapindex\Model\OwnerModel;
use Midnet\Model\Uuid;
use Zend\ServiceManager\Factory\FactoryInterface;

class OwnerFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $uuid = new Uuid();
        
        $form = new OwnerForm($uuid->value);
        $model = new OwnerModel();
        $form->setInputFilter($model->getInputFilter());
        $form->initialize();
        return $form;
    }
}