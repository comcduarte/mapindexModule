<?php 
namespace Mapindex\Controller;

use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Where;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

class AbstractBaseController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $model;
    public $form;
    
    public function indexAction()
    {
        $records = $this->model->fetchAll(new Where());
        
        $paginator = new Paginator(new ArrayAdapter($records));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        
        $count = $this->params()->fromRoute('count', 15);
        $paginator->setItemCountPerPage($count);
        
        return ([
            'data' => $records,
            'count' => $count,
            'primary_key' => $this->model->getPrimaryKey(),
        ]);
    }
    
    public function createAction()
    {
        $request = $this->getRequest();
        $this->form->bind($this->model);
        
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            $this->form->setData($post);
            
            if ($this->form->isValid()) {
                $this->model->create();
                
                $this->flashmessenger()->addSuccessMessage('Add New Record Successful');
            } else {
                $this->flashmessenger()->addErrorMessage("Form is Invalid.");
            }
            
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        return ([
            'form' => $this->form,
        ]);
    }
    
    public function updateAction()
    {
        $primary_key = $this->params()->fromRoute(strtolower($this->model->getPrimaryKey()),0);
        if (!$primary_key) {
            $this->flashmessenger()->addErrorMessage("Unable to retrieve record. Value not passed.");
            
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $this->model->read([$this->model->getPrimaryKey() => $primary_key]);
        
        $this->form->bind($this->model);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->form->setData($data);
            
            if ($this->form->isValid()) {
                $this->model->update();
                
                $this->flashmessenger()->addSuccessMessage('Update Successful');
                
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
            }
            $this->flashmessenger()->addErrorMessage("Form submission was invalid.");
        }
        
        return ([
            'form' => $this->form,
            'primary_key' => $this->model->getPrimaryKey(),
        ]);
    }
    
    public function deleteAction()
    {
        $primary_key = $this->getPrimaryKey();
        $this->model->read([$this->model->getPrimaryKey() => $primary_key]);
        $this->model->delete();
        
        $this->flashmessenger()->addSuccessMessage("Record Deleted.");
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function getModel()
    {
        return $this->model;
    }
    
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }
    
    public function getForm()
    {
        return $this->form;
    }
    
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }
    
    private function getPrimaryKey()
    {
        $primary_key = $this->params()->fromRoute(strtolower($this->model->getPrimaryKey()),0);
        if (!$primary_key) {
            $this->flashmessenger()->addErrorMessage("Unable to retrieve record. Value not passed.");
            
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        return $primary_key;
    }
}
