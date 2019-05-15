<?php 
namespace MapIndex\Form\Factory;

use Interop\Container\ContainerInterface;
use Midnet\Model\Uuid;
use Mapindex\Form\MapindexForm;
use Mapindex\Model\MapindexModel;
use Zend\ServiceManager\Factory\FactoryInterface;

class MapindexFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $uuid = new Uuid();
        
        $form = new MapindexForm($uuid->value);
        
        $model = new MapindexModel();
        $form->setInputFilter($model->getInputFilter());
        $form->setDbAdapter($container->get('mapindex-model-primary-adapter'));
        $form->initialize();
        return $form;
    }
}