<?php
namespace TeaminmediasPluswerk\KeSearch\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *  (c) 2020 Christian Bülter
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * Hooks for ke_search
 * @author Christian Bülter
 * @package TYPO3
 * @subpackage ke_search
 */
class FilterRepository {
    /**
     * @var string
     */
    protected $tableName = 'tx_kesearch_filters';

    public function findByUid($uid)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->tableName);
        return $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
    }

    /**
     * @param integer $uid
     * @param array $updateFields
     * @return mixed
     */
    public function update($uid, $updateFields)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->tableName);
        $queryBuilder
            ->update($this->tableName)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            );
        foreach ($updateFields as $key => $value) {
            $queryBuilder->set($key, $value);
        }
        return $queryBuilder->execute();
    }

    /**
     * Fetches all filters which contain a given filter option
     *
     * @param $filterOptionUid
     * @return mixed[]
     */
    public function findByAssignedFilterOption($filterOptionUid) {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->tableName);
        return $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->inSet(
                    'options',
                    intval($filterOptionUid)
                )
            )
            ->execute()
            ->fetchAll();
    }

    /**
     * remove filter option from filters where it is used
     * @param $filterOptionUid
     */
    public function removeFilterOptionFromAllFilters($filterOptionUid)
    {
        $filters = $this->findByAssignedFilterOption($filterOptionUid);
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $updateFields = [
                    'options' => GeneralUtility::rmFromList($filterOptionUid, $filter['options'])
                ];
                $this->update($filter['uid'], $updateFields);
            }
        }
    }

    public function removeFilterOptionFromFilter($filterOptionUid, $filterUid)
    {
        $filter = $this->findByUid($filterUid);
        $updateFields = [
            'options' => GeneralUtility::rmFromList($filterOptionUid, $filter['options'])
        ];
        $this->update($filter['uid'], $updateFields);
    }

}
