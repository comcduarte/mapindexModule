<?php 
namespace Mapindex\Controller;

use Mapindex\Form\SearchForm;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Join;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Zend\View\Model\ViewModel;
use RuntimeException;

class OwnerController extends AbstractBaseController
{
    public function updateAction()
    {
        $primary_key = $this->params()->fromRoute(strtolower($this->model->getPrimaryKey()),0);
        if (!$primary_key) {
            $this->flashmessenger()->addErrorMessage("Unable to retrieve record. Value not passed.");
            
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $view = new ViewModel();
        $view = parent::updateAction();
        
        /****************************************
         *          Retrieve Maps Subtable
         ****************************************/
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->columns(['UUID'])
            ->from('maps_owners')
            ->join('maps', 'maps_owners.MAP = maps.UUID', ['UUID_M'=>'UUID', 'Index'=>'MAP', 'Street'=>'STREET'], Join::JOIN_INNER)
            ->where([new Like('maps_owners.OWNER', $primary_key)]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $maps = $resultSet->toArray();
        
        $view->setVariable('maps', $maps);
        return($view);
    }
    
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
    
    public function searchAction()
    {
        $view = new ViewModel();
        $data = [];
        
        $searchform = new SearchForm();
        $searchform->initialize();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $data = $request->getPost();
            $searchform->setData($data);
            
            if ($searchform->isValid()) {
                /****************************************
                 *          Create Select Object
                 ****************************************/
                $sql = new Sql($this->adapter);
                
                $select = new Select();
                $select->columns(['UUID','NAME']);
                $select->from($this->model->getTableName());
                
                
                /****************************************
                 *            Search Parameters
                 ****************************************/
                $search_string = NULL;
                if (stripos($data['SEARCH'],'%')) {
                    $search_string = $data['SEARCH'];
                } else {
                    $search_string = '%' . $data['SEARCH'] . '%';
                }
                
                $predicate = new Where();
                $predicate->like('NAME', $search_string);
                
                $select->where($predicate);
                $select->order('NAME');
                
                /****************************************
                 *            Execute Query
                 ****************************************/
                $statement = $sql->prepareStatementForSqlObject($select);
                $resultSet = new ResultSet();
                
                try {
                    $results = $statement->execute();
                    $resultSet->initialize($results);
                } catch (RuntimeException $e) {
                    return $e;
                }
                
                $data = $resultSet->toArray();
            }
        }
        
        $view->setVariables([
            'form' => $searchform,
            'data' => $data,
        ]);
        return $view;
    }
}