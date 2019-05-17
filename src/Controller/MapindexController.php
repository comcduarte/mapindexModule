<?php 
namespace Mapindex\Controller;

use Mapindex\Form\MapindexAssignOwnerForm;
use Mapindex\Form\SearchForm;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Join;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\View\Model\ViewModel;
use RuntimeException;


class MapindexController extends AbstractBaseController
{
    public function indexAction()
    {
        /****************************************
         * Use Base Index Action
         * Saving modifications for later review.
         ****************************************/
        $view = new ViewModel();
        $view = parent::indexAction();
        return $view;

        /****************************************/
        $select = new Select();
        $select->from($this->model->getTableName());
        $select->order(['DATE_DRAWN']);
        
        $select->columns(['UUID','MAP','STREET','SEC_STREET','DATE_DRAWN','DATE_FILED']);
        
//         $records = $this->model->fetchAll(new Where());
//         $paginator = new Paginator(new ArrayAdapter($records));

        $paginator = new Paginator(new DbSelect($select, $this->adapter));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        
        $count = $this->params()->fromRoute('count', 10);
        $paginator->setItemCountPerPage($count);
        
//         $header = array_keys($records[0]);
        $header = [
            'Index',
            'Street',
            'Secondary Street',
            'Drawn',
            'Filed',
        ];
        
        return ([
            'data' => $paginator,
            'header' => $header,
            'count' => $count,
            'primary_key' => $this->model->getPrimaryKey(),
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
        
        $view = new ViewModel();
        $view = parent::updateAction();

        /****************************************
         *          Retrieve Owners Subtable
         ****************************************/
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->columns(['UUID'])
            ->from('maps_owners')
            ->join('owners', 'maps_owners.OWNER = owners.UUID', ['UUID_O'=>'UUID', 'Name'=>'NAME'], Join::JOIN_INNER)
            ->where([new Like('MAP', $primary_key)]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
               
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $owners = $resultSet->toArray();
        
        $view->setVariable('owners', $owners);
        
//         $owners = [];
        
//         foreach ($owner_uuids as $uuid) {
//             $model = new OwnerModel($this->adapter);
//             $model->read(['UUID' => $uuid]);
//             $owners[] = 
//                 [
//                 'UUID' => $model->UUID,
//                 'NAME' => $model->NAME,
//                 ]
//             ;
//         }

        $mapindex_assign_owner_form = new MapindexAssignOwnerForm('MAPINDEX_ASSIGN_OWNER');
        $mapindex_assign_owner_form->setDbAdapter($this->adapter);
        $mapindex_assign_owner_form->initialize();
        $mapindex_assign_owner_form->setAttribute('action', $this->url()->fromRoute('maps/default', ['action' => 'assign']));
        $mapindex_assign_owner_form->get('MAP')->setValue($primary_key);
        $view->setVariable('mapindex_assign_owner_form', $mapindex_assign_owner_form);
        
        
        
        return($view);
    }
    
    public function assignAction()
    {
        $form = new MapindexAssignOwnerForm('MAPINDEX_ASSIGN_OWNER');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $data = $request->getPost();
                $this->model->read(['UUID' => $data['MAP']])->assign($data['OWNER']);
                
                $this->flashmessenger()->addSuccessMessage('Successfully assigned owner to map');
            }
        }
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function unassignAction()
    {
        $join_uuid = $this->params()->fromRoute('uuid',0);
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
                $select->columns(['UUID','MAP','STREET','SEC_STREET']);
                $select->join('owners', 'maps.OWNER = owners.UUID', ['Name' => 'NAME'], Join::JOIN_INNER);
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
                $predicate->like('STREET', $search_string)->or->like('SEC_STREET', $search_string);
                
                $select->where($predicate);
                $select->order('MAP');
                
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