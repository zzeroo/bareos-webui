<?php

/**
 *
 * bareos-webui - Bareos Web-Frontend
 * 
 * @link      https://github.com/bareos/bareos-webui for the canonical source repository
 * @copyright Copyright (c) 2013-2014 dass-IT GmbH (http://www.dass-it.de/)
 * @license   GNU Affero General Public License (http://www.gnu.org/licenses/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Fileset\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Bareos\Db\Sql\BareosSqlCompatHelper;

class FilesetTable implements ServiceLocatorAwareInterface
{

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
                $this->serviceLocator = $serviceLocator;
        }

        public function getServiceLocator() {
                return $this->serviceLocator;
        }

        public function getDbDriverConfig() {
                $config = $this->getServiceLocator()->get('Config');
                return $config['db']['driver'];
        }

	public function fetchAll($paginated=false) 
	{
		$bsqlch = new BareosSqlCompatHelper($this->getDbDriverConfig());
		$select = new Select();
		$select->from($bsqlch->strdbcompat("FileSet"));
		
		if($paginated) {
			$resultSetPrototype = new ResultSet();
			$resultSetPrototype->setArrayObjectPrototype(new Fileset());
			$paginatorAdapter = new DbSelect(
						$select,
						$this->tableGateway->getAdapter(),
						$resultSetPrototype
					);
			$paginator = new Paginator($paginatorAdapter);
			return $paginator;
		}

		$resultSet = $this->tableGateway->select();
		return $resultSet;
	
	}

	public function getFileset($id)
	{
		$bsqlch = new BareosSqlCompatHelper($this->getDbDriverConfig());
		$id = (int) $id;
		$rowset = $this->tableGateway->select(array(
			$bsqlch->strdbcompat("FileSetId") => $id));
		$row = $rowset->current();
		if(!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function getFilesetHistory($id)
	{
		$fset = $this->getFileSet($id);
                $bsqlch = new BareosSqlCompatHelper($this->getDbDriverConfig());  
                $select = new Select();
                $select->from($bsqlch->strdbcompat("FileSet"));
                $select->where($bsqlch->strdbcompat("FileSet") . " = '". $fset->fileset . "'");
                $select->order($bsqlch->strdbcompat("CreateTime") . " DESC");
                
                $resultSet = $this->tableGateway->selectWith($select);
                  
                return $resultSet;
        }
	
	public function getFilesetNum()
	{
		$bsqlch = new BareosSqlCompatHelper($this->getDbDriverConfig());
		$select = new Select();
		$select->from($bsqlch->strdbcompat("FileSet"));
		$resultSetPrototype = new ResultSet();
		$resultSetPrototype->setArrayObjectPrototype(new Fileset());
		$rowset = new DbSelect(
			$select,
			$this->tableGateway->getAdapter(),
			$resultSetPrototype
		);
		$num = $rowset->count();		  
		return $num;
	}
	
}

