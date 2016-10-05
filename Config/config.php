<?php
/**
 * @package     ThirdSetMauticResetBundle
 * @copyright   2016 Third Set Productions. All rights reserved.
 * @author      Third Set Productions
 * @link        http://www.thirdset.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return array(
    'name'        => 'Reset',
    'description' => 'Allows for recurring campaigns by using "reset" tags and a reset command.',
    'version'     => '1.0',
    'author'      => 'Third Set Productions',
    'services'    => array(
        'other'   => array(
            //MANAGERS
            'plugin.thirdset.reset.lead_manager' => array(
                'class'     => 'MauticPlugin\ThirdSetMauticResetBundle\Model\LeadManager',
                'arguments' => 'doctrine.orm.entity_manager'
            ),
            'plugin.thirdset.reset.tag_manager' => array(
                'class'     => 'MauticPlugin\ThirdSetMauticResetBundle\Model\TagManager',
                'arguments' => 'doctrine.orm.entity_manager'
            ),
            'plugin.thirdset.reset.campaign_event_log_manager' => array(
                'class'     => 'MauticPlugin\ThirdSetMauticResetBundle\Model\CampaignEventLogManager',
                'arguments' => 'doctrine.orm.entity_manager'
            ),
            //COMMANDS
            'plugin.thirdset.reset.process_resets_command' => array(
                'class'     => 'MauticPlugin\ThirdSetMauticResetBundle\Command\ProcessResetsCommand',
                'arguments' => [
                        'mautic.factory', 
                        'plugin.thirdset.reset.lead_manager',
                        'plugin.thirdset.reset.tag_manager',
                        'plugin.thirdset.reset.campaign_event_log_manager',
                    ],
                'tag'       => 'console.command',
            ),
        )
    ),
);