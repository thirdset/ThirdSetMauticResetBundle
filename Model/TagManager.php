<?php

namespace MauticPlugin\ThirdSetMauticResetBundle\Model;

use Doctrine\ORM\EntityManager;

/**
 * The TagManager class contains custom methods for managing Tags.
 * 
 * @package ThirdSetMauticResetBundle
 * @since 1.0
 */
class TagManager
{  
    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;
    
    /**
     * Constructor.
     * @param Doctrine\ORM\EntityManager $em
     */
    public function __construct(
                        EntityManager $em
                    )
    {   
        $this->em = $em;
    }
    
    /**
     * Search the list of tags for the passed search string.
     * @param type $search A string to search for (ex: 'reset_%').
     * @return array Returns an array of \Mautic\LeadBundle\Entity\Tags.
     */
    public function searchTags( $search ) 
    {   
        /* @var $query \Doctrine\ORM\Query */
        $query = $this->em->getRepository('MauticLeadBundle:Tag')
                ->createQueryBuilder('t')
                ->where('t.tag LIKE :search')
                ->setParameter('search', $search)
                ->getQuery();

        $results = $query->getResult();

        return $results;
    }
    
    /**
     * Delete orphan tags that are not associated with any lead.
     * This function was copied from the \Mautic\LeadBundle\Entity\TagRepository
     * class.  We modified it slightly to better handle the no orphan tags
     * scenario.
     */
    public function deleteOrphanTags()
    {
        $qb  = $this->em->getConnection()->createQueryBuilder();
        $havingQb = $this->em->getConnection()->createQueryBuilder();

        $havingQb->select('count(x.lead_id) as the_count')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags_xref', 'x')
            ->where('x.tag_id = t.id');

        $qb->select('t.id')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags', 't')
            ->having(sprintf('(%s)', $havingQb->getSQL()) . ' = 0');
        $delete = $qb->execute()->fetch();
        
        if( (! empty($delete)) && (count($delete)) ) {
            $qb->resetQueryParts();
            $qb->delete(MAUTIC_TABLE_PREFIX.'lead_tags')
                ->where(
                    $qb->expr()->in('id', $delete)
                )
                ->execute();
        }
    }
}
