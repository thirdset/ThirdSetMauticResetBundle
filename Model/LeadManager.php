<?php

namespace MauticPlugin\ThirdSetMauticResetBundle\Model;

/**
 * The LeadManager class contains custom methods for managing leads.
 * 
 * @package ThirdSetMauticResetBundle
 * @since 1.0
 */
class LeadManager
{
    /* @var $em \Mautic\CoreBundle\Factory\MauticFactory */
    private $factory;
    
    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;
    
    /**
     * Constructor.
     */
    public function __construct($factory)
    {   
        $this->factory = $factory;
        $this->em = $factory->getEntityManager();
    }
    
    /**
     * Gets all leads that are tagged with the passed Tag.
     * @param \Mautic\LeadBundle\Entity\Tag $tag
     * @return array Returns an array of \Mautic\LeadBundle\Entity\Tags.
     */
    private function getLeadsTaggedWith( \Mautic\LeadBundle\Entity\Tag $tag ) 
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
