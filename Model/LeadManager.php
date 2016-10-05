<?php

namespace MauticPlugin\ThirdSetMauticResetBundle\Model;

use \Mautic\CoreBundle\Factory\MauticFactory;

/**
 * The LeadManager class contains custom methods for managing Leads.
 * 
 * @package ThirdSetMauticResetBundle
 * @since 1.0
 */
class LeadManager
{
    /* @var $factory \Mautic\CoreBundle\Factory\MauticFactory */
    private $factory;
    
    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;
    
    /**
     * Constructor.
     * @param \Mautic\CoreBundle\Factory\MauticFactory $factory
     */
    public function __construct(
                        MauticFactory $factory
                    )
    {   
        $this->factory = $factory;
        $this->em = $factory->getEntityManager();
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
