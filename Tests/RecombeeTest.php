<?php
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Tests\Recombeee;

use Mautic\CoreBundle\Test\AbstractMauticTestCase;
use Mautic\SmsBundle\Sms\TransportChain;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RecombeeeTest extends AbstractMauticTestCase
{
    private $leadModel;


    public function setUp()
    {
        parent::setUp();
        $this->leadModel = $this->container->get('mautic.lead.model.lead');

    }

    public function testProcess()
    {


        $leadEmail = 'rafoxesi@loketa.com';
        $firstname = 'Testname';
        $lastname  = 'Testlastname';

        $leadFields              = [];
        $leadFields['email']     = $leadEmail;
        $leadFields['firstname'] = $firstname;
        $leadFields['lastname']  = $lastname;
        $lead                    = $this->leadModel->checkForDuplicateContact($leadFields);
        $this->assertNotNull($lead);

        /** @var ApiCommands $apiCommand */
        $apiCommand = $this->container->get('mautic.recombee.service.api.commands');
        // import item
        $apiCommand->ImportItems($this->getItems()[0]);
        $this->assertEquals($apiCommand->getCommandOutput(), 'ok');
    }

    private function getItems()
    {
        $items             = [];
        $items[0]['id']    = 1;
        $items[0]['namen'] = 'Test product';
        $items[0]['url']   = 'http://recombee.com';
        $items[1]['id']    = 2;
        $items[1]['namen'] = 'Test product 2';
        $items[1]['url']   = 'http://recombee.com';

        return $items;
    }

}
