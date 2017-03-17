<?php

namespace ArusEntityLootBundle\Repository;

use Actarus\Utils;


/**
 * ArusEntityLootRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArusEntityLootRepository extends \Doctrine\ORM\EntityRepository
{
	public function search( $data, $offset=null, $limit=null )
	{
		$data = Utils::array2object( $data, 'ArusEntityLootBundle\Entity\Search' );
		
		$t_params = array();
		$qb = $this->_em->createQueryBuilder();
		
		if( $offset < 0 ) {
			$offset = null;
			$count  = true;
			$query  = $qb->select( 'count(el.id)' );
		} else {
			$count  = false;
			$query  = $qb->select( array('el') );
		}
		$query = $query->from('ArusEntityLootBundle:ArusEntityLoot','el');
		
		if( $data )
		{
			if ($data->getId()) {
				$query->andWhere('el.id=:id');
				$t_params['id'] = $data->getId();
			}
			if ($data->getEntityType()) {
				$query->andWhere('el.entityId LIKE :entity_type');
				$t_params['entity_type'] = $data->getEntityType().'%';
			}
			if ($data->getEntityId()) {
				$query->andWhere('el.entityId LIKE :entity_id');
				$t_params['entity_id'] = $data->getEntityId();
			}
			if ($data->getDescr()) {
				$query->andWhere('el.descr LIKE :descr');
				$t_params['descr'] = '%'.$data->getDescr().'%';
			}
			if ($data->getMinCreatedAt()) {
				$query->andWhere('el.createdAt >= :min_created_date');
				$t_params['min_created_date'] = date( 'Y-m-d 00:00:00', Utils::dateFR2Time($data->getMinCreatedAt()) );
			}
			if ($data->getMaxCreatedAt()) {
				$query->andWhere('el.createdAt <= :max_created_date');
				$t_params['max_created_date'] = date( 'Y-m-d 23:59:59', Utils::dateFR2Time($data->getMaxCreatedAt()) );
			}
		}
		
		$query->setParameters( $t_params );
		$query->orderBy('el.id', 'DESC');
		if( !is_null($limit) ) {
			$query->setMaxResults( $limit );
		}
		if( !is_null($offset) ) {
			$query->setFirstResult($offset);
		}
		
		$t_result = $query->getQuery()->getResult();
		
		if( $count ) {
			return (int)$t_result[0][1];
		} else {
			return $t_result;
		}
	}
	
	
	public function getRelatedEntity( $loot, $t_entity_type )
	{
		$type = substr( $loot->getEntityId(), 0, 1 );
		$entity = 'Arus'.ucfirst($t_entity_type[$type]);
		$related = $this->_em->getRepository($entity.'Bundle:'.$entity)->findOneByEntityId( $loot->getEntityId() );
	
		return $related; 
	}
	
	
	public function deleteEntity( $entity )
	{
		$qb = $this->createQueryBuilder( 'el' );
		$isDeleted = $qb->delete()->where( 'el.entityId LIKE :entity_id' )->setParameter( 'entity_id',$entity->getEntityId() )->getQuery()->execute();
		return $isDeleted;
	}
}