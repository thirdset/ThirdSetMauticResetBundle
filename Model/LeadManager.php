<?php

namespace MauticPlugin\ThirdSetMauticResetBundle\Model;

use Doctrine\ORM\EntityManager;

/**
 * The LeadManager class contains custom methods for managing Leads.
 * 
 * @package ThirdSetMauticResetBundle
 * @since 1.0
 */
class LeadManager
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
     * Gets all leads that are tagged with the passed Tag.
     * @param \Mautic\LeadBundle\Entity\Tag $tag
     * @return array Returns an array of \Mautic\LeadBundle\Entity\Tags.
     */
    public function getLeadsTaggedWith( \Mautic\LeadBundle\Entity\Tag $tag ) 
    {   
        /* @var $query \Doctrine\ORM\Query */
        $query = $this->em->getRepository('MauticLeadBundle:Lead')
                ->createQueryBuilder('l')
                ->leftJoin('l.tags', 't')
                ->where('t.id = :tagId')
                ->setParameter('tagId', $tag->getId())
                ->getQuery();

        $results = $query->getResult();

        return $results;
    }
}
