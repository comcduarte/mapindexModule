<?php 
namespace Mapindex\Controller;

use Annotation\Model\AnnotationModel;
use Mapindex\Form\UploadFileForm;
use Mapindex\Model\MapindexModel;
use Mapindex\Model\OwnerModel;
use Midnet\Model\Uuid;
use PHPUnit\Framework\Constraint\FileExists;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Validator\Db\RecordExists;
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
        
        
        $this->flashMessenger()->addSuccessMessage('Database tables cleared.');
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
        ini_set('memory_limit', '512M');
        
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
                
                //-- Database Validators --//
                $validator = new RecordExists([
                    'table' => $owner->getTableName(),
                    'field' => 'NAME',
                    'adapter' => $this->adapter,
                ]);
                $validator->setAdapter($this->adapter);
                $validator->setField('NAME');
                $validator->setTable($owner->getTableName());
                
                $file_validator = new FileExists();
                
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
//                     while (($record = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    while ($s = fgets($handle, 1024)) {
                        $record = [];
                        /****************************************
                         * Begin Fixed Width Parsing
                         ****************************************/
                        $record[$MAP] = trim(substr($s, 11, 10));
                        $record[$OWNER] = trim(substr($s, 20, 30));
                        $record[$NUMBER] = trim(substr($s, 51, 6));
                        $record[$STREET] = trim(substr($s, 58, 20));
                        $record[$SEC_STREET] = trim(substr($s, 79, 20));
                        $record[$DATE_DRAWN] = trim(substr($s, 100, 11));
                        $record[$DATE_FILED] = trim(substr($s, 112, 11));
                        
                        
                        /****************************************
                         * If KEY is null, this is a blank line, skip and continue.
                         ****************************************/
                        if ($record[$OWNER] == '') {
                            continue;
                        }
                        switch ($record[$MAP]) {
                            case '':
                                //-- Added Owner to Previous Map --//
                                if ($validator->isValid($record[$OWNER])) {
                                    //-- Owner already exists in table --//
                                    $owner->read(['NAME' => $record[$OWNER]]);
                                } else {
                                    //-- New owner needs to be created --//
                                    $owner->UUID = $uuid->generate()->value;
                                    $owner->NAME = $record[$OWNER];
                                    $owner->STATUS = $owner::ACTIVE_STATUS;
                                    $owner->DATE_CREATED = $today;
                                    
                                    $owner->create();
                                }
                                
                                $index->assign($owner->UUID);
                                continue 2;
                                break;
                            case 'MAP':
                            case '----------':
                                //-- Header --//
                                continue 2;
                            default:
                                break;
                        }
                        
                        /****************************************
                         *              Owner Model
                         ****************************************/
                        if ($validator->isValid($record[$OWNER])) {
                            //-- Owner already exists in table --//
                            $owner->read(['NAME' => $record[$OWNER]]);
                        } else {
                            //-- New owner needs to be created --//
                            $owner->UUID = $uuid->generate()->value;
                            $owner->NAME = $record[$OWNER];
                            $owner->STATUS = $owner::ACTIVE_STATUS;
                            $owner->DATE_CREATED = $today;
                            
                            $owner->create();
                        }
                        
                        /****************************************
                         *            Map Index Model
                         ****************************************/
                        $index->MAP = $record[$MAP];
                        $index->OWNER = $owner->UUID;
                        $index->NUMBER = $record[$NUMBER];
                        $index->STREET = $record[$STREET];
                        $index->SEC_STREET = $record[$SEC_STREET];
                        
                        $drawn = \DateTime::createFromFormat('d-M-Y', $record[$DATE_DRAWN]);
                        $index->DATE_DRAWN = date_format($drawn, 'Y-m-d');
                        
                        $filed = \DateTime::createFromFormat('d-M-Y', $record[$DATE_FILED]);
                        $index->DATE_FILED = date_format($filed, 'Y-m-d');
                        
//                         $index->DATE_DRAWN = date('Y-m-d H:i:s',strtotime($record[$DATE_DRAWN]));
//                         $index->DATE_FILED = date('Y-m-d H:i:s',strtotime($record[$DATE_FILED]));
                        
                        $index->UUID = $uuid->generate()->value;
                        $index->DATE_CREATED = $today;
                        $index->STATUS = $index::ACTIVE_STATUS;
                        
                        $previous_uuid = $index->UUID;
                        
                        $index->create();
                        
                        $index->assign($owner->UUID);
                        
                        /****************************************
                         *            Temporary Break
                         ****************************************/
                         $row++;
                         if ($data['BREAK'] == 'yes' && $row >= $data['BREAK_ON']) {
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