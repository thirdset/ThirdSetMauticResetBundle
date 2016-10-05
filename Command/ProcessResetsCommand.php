<?php

namespace MauticPlugin\ThirdSetMauticResetBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Mautic\CoreBundle\Factory\MauticFactory;
use MauticPlugin\ThirdSetMauticResetBundle\Model\LeadManager;
use MauticPlugin\ThirdSetMauticResetBundle\Model\TagManager;
use MauticPlugin\ThirdSetMauticResetBundle\Model\CampaignEventLogManager;

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
    
    /* @var $campaignModel \Mautic\CampaignBundle\Model\CampaignModel */
    private $campaignModel;
        
    /* @var $leadModel \Mautic\LeadBundle\Model\LeadModel */
    private $leadModel;

    /* @var $leadRepo \Mautic\LeadBundle\Entity\LeadRepository */
    private $leadRepo;
    
    /* @var $leadManager \MauticPlugin\ThirdSetMauticResetBundle\Model\LeadManager */
    private $leadManager;
    
    /* @var $tagManager \MauticPlugin\ThirdSetMauticResetBundle\Model\TagManager */
    private $tagManager;
    
    /* @var $eventLogManager \MauticPlugin\ThirdSetMauticResetBundle\Model\CampaignEventLogManager */
    private $eventLogManager;
    
    /**
     * Constructor.
     * @param \Mautic\CoreBundle\Factory\MauticFactory $factory
     * @param \MauticPlugin\ThirdSetMauticResetBundle\Model\LeadManager $leadManager
     * @param \MauticPlugin\ThirdSetMauticResetBundle\Model\TagManager $tagManager
     * @param \MauticPlugin\ThirdSetMauticResetBundle\Model\CampaignEventLogManager $eventLogManager
     */
    public function __construct(
                MauticFactory $factory,
                LeadManager $leadManager,
                TagManager $tagManager,
                CampaignEventLogManager $eventLogManager
            )
    {
        parent::__construct();
        
        $this->factory = $factory;
        $this->em = $factory->getEntityManager();
        $this->campaignModel = $this->factory->getModel('campaign');
        $this->leadModel = $this->factory->getModel('lead.lead');
        $this->leadRepo = $this->leadModel->getRepository();
        
        $this->leadManager = $leadManager;
        $this->tagManager = $tagManager;
        $this->eventLogManager = $eventLogManager;
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
        
        //get all used reset tags
        $tags = $this->tagManager->searchTags('reset_%');
        
        //loop through each Tag
        /* @var $tag \Mautic\LeadBundle\Entity\Tag */
        foreach($tags AS $tag) {
            
            //ensure that the tag is a recognized format
            if(preg_match('/reset\_(\d+)/', $tag->getTag(), $matches)) {
                
                //pull the campaign id out of the tag
                $campaignId = intval($matches[1]);
                $output->writeln($tag->getId() . ': ' . $tag->getTag() . ':' . $campaignId);
            
                /** @var \Mautic\CampaignBundle\Entity\Campaign $campaign */
                $campaign = $this->campaignModel->getEntity($campaignId);
                
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
                        $fields = $this->leadRepo->getFieldValues($lead->getId());
                        $lead->setFields($fields);
                        
                        //delete the event log for the campaign/lead
                        $output->writeln('deleting the event log for campaign ' . $campaign->getId() . ' for lead ' . $lead->getId() . ' (' . $lead->getEmail() . ').');
                        $deletionCount = $this->eventLogManager->deleteCampaignEventLog($campaign, $lead);
                        $output->writeln($deletionCount . ' events deleted.');
                        
                        //remove the tag from the lead
                        $lead->removeTag($tag);
                        $this->leadModel->saveEntity($lead);
                    }//end for each lead
                } //end if valid campaign
            } //end if valid tag
        } //end for each tag
        
        //delete any orphan tags
        $this->tagManager->deleteOrphanTags();
        
        $output->writeln('Done.');
    } //end function
    
}
