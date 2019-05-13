<?php 
namespace Mapindex\Controller\Factory;

use Interop\Container\ContainerInterface;
use Mapindex\Controller\OwnerController;
use Mapindex\Form\OwnerForm;
use Mapindex\Model\OwnerModel;
use Midnet\Model\Uuid;
use Zend\ServiceManager\Factory\FactoryInterface;

class OwnerControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = $container->get('mapindex-model-primary-adapter');
        $uuid = new Uuid();
        $date = new \DateTime('now',new \DateTimeZone('EDT'));
        $today = $date->format('Y-m-d H:i:s');
        
        $controller = new OwnerController();
        $controller->setDbAdapter($adapter);
        
        $model = new OwnerModel($adapter);
        $model->UUID = $uuid->value;
        $model->DATE_CREATED = $today;
        $model->STATUS = $model::ACTIVE_STATUS;
        
        $controller->setModel($model);
        
        $form = $container->get('FormElementManager')->get(OwnerForm::class);
        $controller->setForm($form);
        
        return $controller;
    }
}