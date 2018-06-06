<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Helper;

use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenFinder;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeTokenReplacer;
use Symfony\Component\Translation\TranslatorInterface;
use Mautic\PageBundle\Event\PageDisplayEvent;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use Recombee\RecommApi\Client;
use Recombee\RecommApi\Exceptions as Ex;
use Recombee\RecommApi\Requests as Reqs;

const NUM                   = 50;
const PROBABILITY_PURCHASED = 0.2;

/**
 * Class RecombeeHelper.
 */
class RecombeeHelper
{
    private $recombeeItemsToUserRegex = '{RecombeeItems=(.*?)}';

    private $recombeeRegex =
        [
            'items' => '{RecombeeItems=(.*?)}',
            'users' => '{RecombeeUsers=(.*?)}',
        ];

    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * @var RecombeeModel $recombeeModel
     */
    protected $recombeeModel;

    /**
     * @var Translator
     */
    protected $translator;


    /**
     * @var Client
     */
    private $client;

    /**
     * @var CorePermissions
     */
    private $security;

    /**
     * RecombeeHelper constructor.
     *
     * @param IntegrationHelper   $integrationHelper
     * @param RecombeeModel       $recombeeModel
     * @param TranslatorInterface $translator
     * @param CorePermissions     $security
     */
    public function __construct(
        IntegrationHelper $integrationHelper,
        RecombeeModel $recombeeModel,
        TranslatorInterface $translator,
        CorePermissions $security
    ) {
        $this->integrationHelper = $integrationHelper;
        $this->recombeeModel     = $recombeeModel;
        $this->translator        = $translator;
        $this->security          = $security;
    }

    /**
     * @return array
     */
    public function getRecombeeTokens()
    {
        $entities = $this->recombeeModel->getRepository()->getEntities();
        $tokens   = [];
        /** @var Recombee $entity */
        foreach ($entities as $entity) {
            if (!$entity->isPublished()) {
                continue;
            }
            $tokenType = 'RecombeeItems';
            if ($entity->getType() == 'users') {
                $tokenType = 'RecombeeUsers';
            }

            $tokens['{'.$tokenType.'='.$entity->getId().'}'] = $entity->getName();
        }

        return $tokens;
    }

    /**
     * Parse contact data do import do recombee
     *
     * @param array $contact
     */
    public function parseContactData(array $contact)
    {
        $contactData = [];
        foreach ($contact['fields'] as $fields) {
            foreach ($fields as $field) {
                $contactData[$field['alias']] = [
                    $field['value'],
                ];
            }
        }
        $contactData['tags'] = $contact['tags'];
        $this->getSetsOfParam($contact, 'owner', ['username', 'firstname', 'lastname'], $contactData);
    }

    /**
     * Get array sets of property
     *
     * @param array $contact
     * @param       $attr
     * @param array $keys
     * @param array $contactData
     */
    private function getSetsOfParam(array $contact, $attr, array $keys, array &$contactData)
    {
        if (!empty($contact[$attr])) {
            foreach ($keys as $key) {
                $contactData[$attr][] = [$key, $contact[$attr][$key]];
            }
        }
    }

    public function pushLead(array $lead)
    {

        if (empty($lead['id'])) {
            return 'no lead';
        }

        try {
            $ret = $this->getClient()->send(new Reqs\SetUserValues($lead['id'], $lead, ['cascadeCreate' => true]));

            return print_r($ret, true);
        } catch (Ex\ApiException $e) {
            return $e->getMessage();
        }
    }

    public function testItemData()
    {
        try {
            $this->getClient()->send(new Reqs\AddPurchase(444, 5, ['cascadeCreate' => true]));
        } catch (Ex\ApiException $e) {
            die($e->getMessage());
        }
    }


    public function importTestData()
    {
        try {
            // Generate some random purchases of items by users
            $purchase_requests = [];
            for ($i = 0; $i < NUM; ++$i) {
                for ($j = 0; $j < NUM; ++$j) {
                    if (mt_rand() / mt_getrandmax() < PROBABILITY_PURCHASED) {
                        $request = new Reqs\AddPurchase(
                            "{$i}", "{$j}",
                            ['cascadeCreate' => true] // Use cascadeCreate to create the
                        // yet non-existing users and items
                        );
                        array_push($purchase_requests, $request);
                    }
                }
            }
            echo "Send purchases\n";
            $res = $this->getClient()->send(
                new Reqs\Batch($purchase_requests)
            ); //Use Batch for faster processing of larger data

            // Get 5 recommendations for user 'user-25'
            $recommended = $this->getClient()->send(new Reqs\UserBasedRecommendation('25', 5));

            echo 'Recommended items: '.implode(',', $recommended)."\n";
        } catch (Ex\ApiException $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return Client
     */
    public function getClient()
    {

        if (!is_object($this->client)) {
            $integration = $this->integrationHelper->getIntegrationObject('Recombee');
            if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
                return;
            }
            if (!empty($_POST['integration_details']['apiKeys'])) {
                $apiKeys = $_POST['integration_details']['apiKeys'];
            } else {
                $apiKeys = $integration->getKeys();
            }

            $database   = $apiKeys['database'];
            $secret_key = $apiKeys['secret_key'];

            if (!is_object($this->client)) {
                $this->client = new Client(
                    $database, $secret_key
                );
            }
        }

        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }


    public function tokenReplace($event)
    {
        $content = $event->getContent();
        $regex   = '/'.$this->recombeeRegex.'/i';

        preg_match_all($regex, $content, $matches);

        if (count($matches[0])) {
            foreach ($matches[1] as $k => $id) {
                $entity = $this->recombeeModel->getEntity($id);
                if ($entity !== null &&
                    (
                        $entity->isPublished(false) ||
                        $this->security->hasEntityAccess(
                            'recombee:recombee:viewown',
                            'recombee:recombee:viewother',
                            $entity->getCreatedBy()
                        )
                    )
                ) {
                    $html = ($entity->isPublished()) ? $entity->getId() :
                        '<div class="mauticform-error">'.
                        $this->translator->trans('mautic.plugin.recombee.pagetoken.notpublished').
                        '</div>';


                    $content = str_replace('{recombee='.$id.'}', $html, $content);
                } else {
                    $content = str_replace('{recombee='.$id.'}', '', $content);
                }
            }
        }
        $event->setContent($content);
    }

    /**
     * @return string
     */
    public function getRecombeeRegex()
    {
        return $this->recombeeRegex;
    }


    /**
     * @param Recombee $entity
     *
     * @return array
     */
    public function getRecombeeKeysFromEntity(Recombee $entity)
    {
        $params = [];
        if (strpos($entity->getType(), 'RecommendUsers') !== false) {
            $params['listClass']         = 'Recombee\RecommApi\Requests\ListUsers';
            $params['listPropertyClass'] = 'Recombee\RecommApi\Requests\ListUserProperties';
            $params['key']               = 'userId';
            $params['filter']            = 'fiter';
            $params['search']            = 'email';
            $params['token']             = 'recombeeUserField';
        } else {
            $params['listClass']         = 'Recombee\RecommApi\Requests\ListItems';
            $params['listPropertyClass'] = 'Recombee\RecommApi\Requests\ListItemProperties';
            $params['key']               = 'itemId';
            $params['filter']            = 'fiter';
            $params['search']            = 'name';
            $params['token']             = 'recombeeItemField';
        }

        return $params;
    }
}
