<?php

namespace MauticPlugin\ThirdSetMauticResetBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The ProcessResetsCommand does the following:
 * * Finds all leads with a reset tag (see README.md for examples).
 * * Gets the campaign id out of the tag.
 * * Clears the campaign_lead_event_log history for the lead/campaign so that
 *   the lead can go through the campaign again.
 * 
 * @package ThirdSetMauticResetBundle
 * @since 1.0
 */
class ProcessResetsCommand extends ModeratedCommand
{
    
    /* @var $em \Mautic\CoreBundle\Factory\MauticFactory */
    private $factory;
    
    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;
    
    private $leadManager;
    
    /**
     * Constructor.
     */
    public function __construct(
                        $factory,
                        $leadManager
                    )
    {
        parent::__construct();
        
        $this->factory = $factory;
        $this->em = $factory->getEntityManager();
        $this->leadManager = $leadManger;
    }
    
    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName('mautic:campaigns:process_resets')
            ->setDescription('Process any leads tagged with a reset tag (clears their campaign specific history and then removes the tag).')
        ;
    }

    /**
     * Executes the command.
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Processing reset tags...');
        
        /* @var $campaignModel \Mautic\CampaignBundle\Model\CampaignModel */
        $campaignModel = $this->factory->getModel('campaign');
        
        /* @var $leadModel \Mautic\LeadBundle\Model\LeadModel */
        $leadModel = $this->factory->getModel('lead.lead');
        
        /* @var $leadRepo \Mautic\LeadBundle\Entity\LeadRepository */
        $leadRepo = $leadModel->getRepository();
        
        //get all used reset tags
        $tags = $this->searchTags('reset_%');
        
        //loop through each Tag
        /* @var $tag \Mautic\LeadBundle\Entity\Tag */
        foreach($tags AS $tag) {
            
            //ensure that the tag is a recognized format
            if(preg_match('/reset\_(\d+)/', $tag->getTag(), $matches)) {
                
                //pull the campaign id out of the tag
                $campaignId = intval($matches[1]);
                $output->writeln($tag->getId() . ': ' . $tag->getTag() . ':' . $campaignId);
            
                /** @var \Mautic\CampaignBundle\Entity\Campaign $campaign */
                $campaign = $campaignModel->getEntity($campaignId);
                
                if($campaign == null) {
                    $output->writeln('ERROR: couldn\'t find campaign with id ' . $campaignId);
                } else {
                    $output->writeln($campaign->getId());

                    //get an array of all leads that are tagged with the tag.
                    $leads = $this->leadManager->getLeadsTaggedWith( $tag );

                    $output->writeln('found ' . sizeof($leads) . ' lead(s) tagged with "' . $tag->getTag() . '".');

                    //loop through each Lead
                    /* @var $lead \Mautic\LeadBundle\Entity\Lead */
                    foreach($leads AS $lead) {
                        //hydrate the lead
                        $fields = $leadRepo->getFieldValues($lead->getId());
                        $lead->setFields($fields);
                        
                        //delete the event log for the campaign/lead
                        $output->writeln('deleting the event log for campaign ' . $campaign->getId() . ' for lead ' . $lead->getId() . '(' . $lead->getEmail() . ')');
                        $deletionCount = $this->deleteCampaignEventLog($campaign, $lead);
                        $output->writeln($deletionCount . ' events deleted.');
                        
                        //remove the tag from the lead
                        $lead->removeTag($tag);
                        $leadModel->saveEntity($lead);
                    }//end for each lead
                } //end if valid campaign
            } //end if valid tag
        } //end for each tag
        
        //delete any orphan tags
        $this->deleteOrphanTags();
        
        $output->writeln('Done.');
    } //end function
    
    /**
     * Search the list of tags for the passed search string.
     * @param type $search A string to search for (ex: 'reset_%').
     * @return array Returns an array of \Mautic\LeadBundle\Entity\Tags.
     */
    private function searchTags( $search ) 
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
    private function deleteCampaignEventLog(
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
