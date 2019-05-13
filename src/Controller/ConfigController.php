<?php 
namespace Mapindex\Controller;

use Annotation\Model\AnnotationModel;
use Mapindex\Form\UploadFileForm;
use Mapindex\Model\MapindexModel;
use Mapindex\Model\OwnerModel;
use Midnet\Model\Uuid;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ConfigController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public function indexAction()
    {
        $importForm = new UploadFileForm('MAPS');
        $importForm->initialize();
        $importForm->addInputFilter();
        
        $data_is_writable = is_writable('data');
        
        return new ViewModel([
            'mapsImportForm' => $importForm,
            'data_is_writable' => $data_is_writable,
        ]);
    }
    
    public function importAction()
    {
        $this->importDatabaseTable();
        return $this->redirect()->toRoute('maps/config');
    }
    
    public function clearAction()
    {
        $this->clearDatabaseTables();
        return $this->redirect()->toRoute('maps/config');
    }
    
    public function createDatabaseTable()
    {
        $sql = "CREATE TABLE `map-index_dev`.`maps` ( `UUID` VARCHAR(36) NOT NULL , `STATUS` INT NULL , `DATE_CREATED` TIMESTAMP NULL , `DATE_MODIFIED` TIMESTAMP NULL , PRIMARY KEY (`UUID`(36))) ENGINE = InnoDB;";
    }
    
    public function clearDatabaseTables()
    {
        $tables = [
            'maps',
            'owners',
            'maps_owners',
        ];
        
        foreach ($tables as $table) {
            $statement = $this->adapter->createStatement("TRUNCATE TABLE `" . $table . "`");
            $statement->execute();
        }
    }
    
    public function importDatabaseTable()
    {
        $form = new UploadFileForm();
        $form->initialize();
        $form->addInputFilter();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                $this->flashMessenger()->addSuccessMessage('Successful Upload: ' . $data['FILE']['tmp_name']);
                
                //-- Set Global Variables --//
                $uuid = new Uuid();
                $date = new \DateTime('now',new \DateTimeZone('EDT'));
                $today = $date->format('Y-m-d H:i:s');
                $year = $date->format('Y');
                $annotation = new AnnotationModel($this->adapter);
                $index = new MapindexModel($this->adapter);
                $owner = new OwnerModel($this->adapter);
                $previous_uuid = null;
                
                
                //-- Define CSV Position variables --//
                $MAP = 0;
                $OWNER = 1;
                $NUMBER = 2;
                $STREET = 3;
                $SEC_STREET = 4;
                $DATE_DRAWN = 5;
                $DATE_FILED = 6;
                
                //-- Begin reading file line by line --//
                $row = 0;
                if (($handle = fopen($data['FILE']['tmp_name'],"r")) !== FALSE) {
                    //-- Read record from file --//
                    while (($record = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        /****************************************
                         * If KEY is null, this is a blank line, skip and continue.
                         ****************************************/
                        switch ($record[$MAP]) {
                            case '':
                                //-- Added Owner to Previous Map --//
                                $owner->UUID = $uuid->generate()->value;
                                $owner->NAME = $record[$OWNER];
                                $owner->STATUS = $owner::ACTIVE_STATUS;
                                $owner->DATE_CREATED = $today;
                                
                                $owner->create();
                                
                                $index->assignOwner($owner->UUID);
                                continue 2;
                                break;
                            default:
                                break;
                        }
                        
                        /****************************************
                         *              Owner Model
                         ****************************************/
                        $owner->UUID = $uuid->generate()->value;
                        $owner->NAME = $record[$OWNER];
                        $owner->STATUS = $owner::ACTIVE_STATUS;
                        $owner->DATE_CREATED = $today;
                        
                        $owner->create();
                        
                        /****************************************
                         *            Map Index Model
                         ****************************************/
                        $index->MAP = $record[$MAP];
                        $index->OWNER = $owner->UUID;
                        $index->NUMBER = $record[$NUMBER];
                        $index->STREET = $record[$STREET];
                        $index->SEC_STREET = $record[$SEC_STREET];
                        $index->DATE_DRAWN = date('Y-m-d H:i:s',strtotime($record[$DATE_DRAWN]));
                        $index->DATE_FILED = date('Y-m-d H:i:s',strtotime($record[$DATE_FILED]));
                        
                        $index->UUID = $uuid->generate()->value;
                        $index->DATE_CREATED = $today;
                        $index->STATUS = $index::ACTIVE_STATUS;
                        
                        $previous_uuid = $index->UUID;
                        
                        $index->create();
                        
                        $index->assignOwner($owner->UUID);
                        
                        
                        
                        
                        $row++;
                        /****************************************
                         *            Temporary Break
                         ****************************************/
                         if ($row >= 15) {
                            break;
                         }
                         /****************************************/
                         
                    }
                    fclose($handle);
                    unlink($data['FILE']['tmp_name']);
                }
            } else {
                $this->flashMessenger()->addErrorMessage('Failure: Form Invalid');
            }
        } 
        
        
    }
}