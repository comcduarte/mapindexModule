<?php 
namespace Mapindex\Controller\Factory;

use Interop\Container\ContainerInterface;
use Mapindex\Controller\MapindexController;
use Mapindex\Form\MapindexForm;
use Mapindex\Model\MapindexModel;
use Midnet\Model\Uuid;
use Zend\ServiceManager\Factory\FactoryInterface;

class MapindexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new MapindexController();
        $uuid = new Uuid();
        $date = new \DateTime('now',new \DateTimeZone('EDT'));
        $today = $date->format('Y-m-d H:i:s');
        
        $adapter = $container->get('mapindex-model-primary-adapter');
        $controller->setDbAdapter($adapter);
        
        $model = new MapindexModel($adapter);
        $model->UUID = $uuid->value;
        $model->DATE_CREATED = $today;
        $model->STATUS = $model::ACTIVE_STATUS;
        
        $controller->setModel($model);
        
        $form = $container->get('FormElementManager')->get(MapindexForm::class);
        $controller->setForm($form);
        return $controller;
    }
}