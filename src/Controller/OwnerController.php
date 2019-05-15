<?php 
namespace Mapindex\Controller;

class OwnerController extends AbstractBaseController
{
    public function assignAction()
    {
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function unassignAction()
    {
        $join_uuid = $this->params()->fromRoute('uuid', 0);
        
        $this->model->unassign(NULL, $join_uuid);
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
}