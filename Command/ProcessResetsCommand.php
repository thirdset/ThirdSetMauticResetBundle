<?php

namespace MauticPlugin\ThirdSetResetBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ProcessResetsCommand. This command does the following:
 * * Finds all leads with a reset tag (see README.md for examples).
 * * Gets the campaign id out of the tag.
 * * Clears the campaign_lead_event_log history for the lead/campaign so that
 *   the lead can go through the campaign again.
 * 
 * @package ThirdSetMauticResetBundle
 * @since 1.0
 */
class ProcessResetsCommand extends Command
{
    
    /**
     * Constructor.
     */
    public function __construct(
                    )
    {
        parent::__construct();
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
    }
}
