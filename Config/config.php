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
            'plugin.thirdset.reset.reset_command' => array(
                'class'     => 'MauticPlugin\ThirdSetResetBundle\Command\ResetCommand',
                'tag'       => 'console.command',
            )
        )
    ),
);