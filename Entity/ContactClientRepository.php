<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactClientBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class ContactClientRepository.
 */
class ContactClientRepository extends CommonRepository
{
    /**
     * Get a list of entities.
     *
     * @param array $args
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getEntities(array $args = [])
    {
        $alias = $this->getTableAlias();

        $q = $this->_em
            ->createQueryBuilder()
            ->select($alias)
            ->from('MauticContactClientBundle:ContactClient', $alias, $alias.'.id');

        if (empty($args['iterator_mode'])) {
            $q->leftJoin($alias.'.category', 'c');
        }

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTableAlias()
    {
        return 'f';
    }

    /**
     * @return array
     */
    public function getSearchCommands()
    {
        return $this->getStandardSearchCommands();
    }

    /**
     * @return array
     */
    public function getContactClientList($currentId)
    {
        $alias = $this->getTableAlias();
        $q     = $this->createQueryBuilder($alias);
        $q->select('partial '.$alias.'.{id, name, description}')->orderBy($alias.'.name');

        return $q->getQuery()->getArrayResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $q
     * @param                                                              $filter
     *
     * @return array
     */
    protected function addCatchAllWhereClause($q, $filter)
    {
        $alias = $this->getTableAlias();

        return $this->addStandardCatchAllWhereClause(
            $q,
            $filter,
            [$alias.'.name', $alias.'.website', $alias.'.description']
        );
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $q
     * @param                                                              $filter
     *
     * @return array
     */
    protected function addSearchCommandWhereClause($q, $filter)
    {
        return $this->addStandardSearchCommandWhereClause($q, $filter);
    }

    /**
     * @return array
     */
    protected function getDefaultOrder()
    {
        return [
            [$this->getTableAlias().'.name', 'ASC'],
        ];
    }

    /**
     * @param $clientId
     *
     * @return array
     */
    public function getPendingEventsData($clientId)
    {
        $data = [];

        $data[] = [1,'CampaignName1', 1, 'EventName1', 5434];
        $data[] = [1,'CampaignName1', 2, 'EventName2', 145];
        $data[] = [2,'CampaignName1', 3, 'EventName3', 9746];
        $data[] = [2,'CampaignName1', 4, 'EventName4', 354];

        return $data;
    }
}
