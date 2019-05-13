<?php 
namespace Mapindex\Model;

use Midnet\Model\DatabaseObject;

class OwnerModel extends DatabaseObject
{
    public $NAME;
    
    public function __construct($dbAdapter = null)
    {
        parent::__construct($dbAdapter);
        
        $this->primary_key = 'UUID';
        $this->table = 'owners';
    }
}