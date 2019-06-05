<?php 
namespace Mapindex\Model;

use Midnet\Model\DatabaseObject;
use Midnet\Model\Uuid;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Exception;

class MapindexModel extends DatabaseObject
{
    public $MAP;
    public $OWNER;
    public $NUMBER;
    public $STREET;
    public $SEC_STREET;
    public $IMAGE_LOC;
    public $DATE_DRAWN;
    public $DATE_FILED;
    
    public function __construct($dbAdapter = null)
    {
        parent::__construct($dbAdapter);
        
        $this->primary_key = 'UUID';
        $this->table = 'maps';
    }
    
    public function assign($owner_uuid)
    {
        $sql = new Sql($this->dbAdapter);
        $uuid = new Uuid();
        
        $columns = [
            'UUID',
            'MAP',
            'OWNER',
        ];
        
        $values = [
            $uuid->value,
            $this->UUID,
            $owner_uuid,
        ];
        
        $insert = new Insert();
        $insert->into('maps_owners');
        $insert->columns($columns);
        $insert->values($values);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        
        try {
            $statement->execute();
        } catch (Exception $e) {
            return $e;
        }
        return $this;
    }
    
    public function unassign($owner_uuid = NULL, $join_uuid = NULL)
    {
        $sql = new Sql($this->dbAdapter);
        
        $delete = new Delete();
        $delete->from('maps_owners');
        
        if ($owner_uuid != NULL ) {
//             $delete->where(['OWNER' => $this->UUID, 'MAP' => $map_uuid]);
        }
        
        if ($join_uuid != NULL) {
            $delete->where(['UUID' => $join_uuid]);
        }
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        
        try {
            $statement->execute();
        } catch (Exception $e) {
            return $e;
        }
        return $this;
    }
    
    public function setImage()
    {
        /****************************************
         *            Retrieve Images
         ****************************************/
        if (!stripos($this->MAP,'-')) {
            $this->flashMessenger()->addErrorMessage('Failure: Unable to retrieve image for ' . $this->MAP);
            return $this;
        }
        
        $img_dir = explode("-", $this->MAP);
        $url = sprintf('http://mimas:8001/%s/map00%s.tif', $img_dir[1], $img_dir[0]);
        if (($imghandle = fopen($url,"r")) !== FALSE) {
            $im = new \Imagick();
            $im->readimage($url);
            $im->setimageformat('jpeg');
            $im->writeimage('./data/maps/temp.jpg');
            $im->destroy();
            
            fclose($imghandle);
        }
        
        return $this;
    }
}