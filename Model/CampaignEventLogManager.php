<?php

namespace MauticPlugin\ThirdSetMauticResetBundle\Model;

use Doctrine\ORM\EntityManager;

/**
 * The LeadManager class contains custom methods for managing Leads.
 * 
 * @package ThirdSetMauticResetBundle
 * @since 1.0
 */
class CampaignEventLogManager
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
     * Deletes the Campaign Events for combination of Lead and Campaign.
     *
     * This is used so that we can rerun the Campaign for the Lead.
     *
     * @param \Mautic\CampaignBundle\Entity\Campaign $campaign The campaign 
     * whose events we want to clear.
     * @param \Mautic\LeadBundle\Entity\Lead $lead The lead whose events we want
     * to clear.
     * @return Returns the number of events that were deleted.
     */
    public function deleteCampaignEventLog(
                        \Mautic\CampaignBundle\Entity\Campaign $campaign, 
                        \Mautic\LeadBundle\Entity\Lead $lead
                    )
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->em->getConnection()->createQueryBuilder();
        
        $deletionCount = $qb->delete(MAUTIC_TABLE_PREFIX . 'campaign_lead_event_log')
            ->where('campaign_id = :campaignId')
            ->andWhere('lead_id = :leadId')
            ->setParameter('campaignId', $campaign->getId())
            ->setParameter('leadId', $lead->getId())
            ->execute();
        
        return $deletionCount;
    }
}
