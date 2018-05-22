<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Api;

use Mautic\PageBundle\Model\TrackableModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Monolog\Logger;
use Recombee\RecommApi\Client;


class RecombeeApi extends AbstractRecombeeApi
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * TwilioApi constructor.
     *
     * @param TrackableModel    $pageTrackableModel
     * @param IntegrationHelper $integrationHelper
     * @param Logger            $logger
     */
    public function __construct(
        TrackableModel $pageTrackableModel,
        IntegrationHelper $integrationHelper,
        Logger $logger
    ) {
        $this->logger = $logger;

        $integration = $integrationHelper->getIntegrationObject('Recombee');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {

            $keys = $integration->getDecryptedApiKeys();

            if (isset($keys['database']) && isset($keys['secret_key'])) {
                $this->client = new Client(
                    $keys['database'],
                    $keys['secret_key'],
                    'https',
                    ['serviceName' => 'Mautic '.$this->get('kernel')->getVersion()]
                );
            }
        }

        parent::__construct($pageTrackableModel);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

}
