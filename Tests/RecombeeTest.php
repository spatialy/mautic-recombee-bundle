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
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\SmsBundle\Sms\TransportChain;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ProcessData;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeToken;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RecombeeeTest extends AbstractMauticTestCase
{
    /**
     * @var RecombeeToken
     */
    protected $recombeeToken;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var ApiCommands
     */
    private $apiCommand;

    /**
     * @var Lead
     */
    private $leadInTest;


    public function setUp()
    {
        parent::setUp();
        $this->leadModel     = $this->container->get('mautic.lead.model.lead');
        $this->apiCommand    = $this->container->get('mautic.recombee.service.api.commands');
        $this->recombeeToken = $this->container->get('mautic.recombee.service.token');
    }

    public function testProcess()
    {
        $lead = $this->createLead();
        if (!$lead->getId()) {
            $this->leadModel->saveEntity($lead);
        }
        $this->leadInTest = $lead;
        $this->assertNotNull($lead);
        $this->assertNotNull($lead->getId());

        $this->apiCommand->ImportItems($this->getItems()[0]);
        $this->assertForSingleApiCall();

        $this->apiCommand->ImportItems($this->getItems());
        $this->assertForMultipleApiCall();

        $this->apiCommand->callCommand(
            'AddDetailView',
            $this->getItemsToEvent(true, true)
        );
        $this->assertForSingleApiCall();

        $this->apiCommand->callCommand(
            'AddDetailView',
            $this->getItemsToEvent()
        );
        $this->assertForMultipleApiCall();

        $this->apiCommand->callCommand(
            'AddCartAddition',
            $this->getItemsToEvent(['id', 'amount', 'price'], true)
        );

        $this->assertForSingleApiCall();

        $this->apiCommand->callCommand(
            'AddCartAddition',
            $this->getItemsToEvent(['id', 'amount', 'price'])
        );

        $this->assertForMultipleApiCall();


        // token userId = 1 itemId = 1, limit = 9
        $this->recombeeToken->setToken(['id' => 1, 'userId' => $this->leadInTest->getId(), 'limit' => 9]);

        // check for any items
        $this->apiCommand->callCommand(
            'RecommendItemsToUser',
            $this->recombeeToken->getOptions(true)
        );
        $this->assertTrue(!empty($this->apiCommand->getCommandOutput()['recomms']));

        //check for abandoned cart
        $this->apiCommand->getAbandonedCart($this->recombeeToken, 0, 3600 * 12);
        $this->assertTrue(!empty($this->apiCommand->getCommandOutput()['recomms']));

        //check for abandoned cart empty
        $this->apiCommand->getAbandonedCart($this->recombeeToken, 0, 0);
        $this->assertTrue(empty($this->apiCommand->getCommandOutput()['recomms']));
        return;
        $this->apiCommand->callCommand(
            'AddPurchase',
            $this->getItemsToEvent(['id', 'amount', 'price', 'profit'], true)
        );

        $this->assertForSingleApiCall();

        $this->apiCommand->callCommand(
            'AddPurchase',
            $this->getItemsToEvent(['id', 'amount', 'price', 'profit'])
        );
        $this->assertForMultipleApiCall();

        $this->apiCommand->getAbandonedCart($this->recombeeToken, 1, 3600 * 12);

        $this->assertTrue(empty($this->apiCommand->getCommandOutput()['recomms']));

    }

    private function assertForSingleApiCall()
    {
        $this->assertEquals($this->apiCommand->getCommandOutput(), 'ok');

    }

    private function assertForMultipleApiCall()
    {
        $this->assertCount(2, $this->apiCommand->getCommandOutput());
        $this->assertArraySubset([0 => ['code' => 200]], $this->apiCommand->getCommandOutput());
    }


    private function createLead()
    {
        $leadEmail = 'kuzmany@gmail.com';
        $firstname = 'Testname';
        $lastname  = 'Testlastname';

        $leadFields              = [];
        $leadFields['email']     = $leadEmail;
        $leadFields['firstname'] = $firstname;
        $leadFields['lastname']  = $lastname;

        return $this->leadModel->checkForDuplicateContact($leadFields);
    }

    private function getLeadData()
    {
        $leadEmail = 'rafoxesi4@loketa.com';
        $firstname = 'Testname';
        $lastname  = 'Testlastname';

        $leadFields              = [];
        $leadFields['email']     = $leadEmail;
        $leadFields['firstname'] = $firstname;
        $leadFields['lastname']  = $lastname;

        return $leadFields;
    }

    private function getItems()
    {
        $items              = [];
        $items[0]['id']     = 1;
        $items[0]['name']   = 'Test product';
        $items[0]['url']    = 'http://recombee.com';
        $items[0]['price']  = '99';
        $items[0]['amount'] = '2';
        $items[0]['profit'] = '19';

        $items[1]['id']     = 2;
        $items[1]['name']   = 'Test product 2';
        $items[1]['price']  = '10';
        $items[1]['amount'] = '2';
        $items[1]['profit'] = '3';

        return $items;
    }

    /**
     * Get Items for tests - single item, multiple item, fill with userId in default
     *
     * @param bool $keysAttr If true We use just itemId and userId
     * @param bool $first
     *
     * @param bool $userId
     *
     * @return array
     */
    private function getItemsToEvent($keysAttr = true, $first = false, $userId = true)
    {
        $returnItems = [];
        if ($keysAttr === true) {
            $keys = ['id'];
        } else {
            $keys = $keysAttr;
        }

        if ($userId === true) {
            $userId = $this->leadInTest->getId();
        }


        foreach ($this->getItems() as $keyFromItems => $item) {
            foreach ($keys as $key) {
                $keyForArray = $key;
                if ($key === 'id') {
                    $keyForArray = 'itemId';
                }
                if ($first == true) {
                    return [$keyForArray => $item[$key], 'userId' => $userId];
                }
                $returnItems[$keyFromItems][$keyForArray] = $item[$key];
            }
            $returnItems[$keyFromItems]['userId'] = $this->leadInTest->getId();
        }

        return $returnItems;
    }

}
