<?php 
namespace Mapindex\Model;

use Midnet\Model\DatabaseObject;
use Midnet\Model\Uuid;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use RuntimeException;

class MapindexModel extends DatabaseObject
{
    public $MAP;
    public $OWNER;
    public $NUMBER;
    public $STREET;
    public $SEC_STREET;
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
        } catch (RuntimeException $e) {
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
        } catch (RuntimeException $e) {
            return $e;
        }
        return $this;
    }
    
}