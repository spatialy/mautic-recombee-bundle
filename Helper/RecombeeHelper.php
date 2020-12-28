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

use Doctrine\ORM\EntityManager;
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

    private $recombeeRegex = '{recombee=(.*?)}';

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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * RecombeeHelper constructor.
     *
     * @param IntegrationHelper   $integrationHelper
     * @param RecombeeModel       $recombeeModel
     * @param TranslatorInterface $translator
     * @param CorePermissions     $security
     * @param EntityManager       $entityManager
     */
    public function __construct(
        IntegrationHelper $integrationHelper,
        RecombeeModel $recombeeModel,
        TranslatorInterface $translator,
        CorePermissions $security,
        EntityManager $entityManager
    ) {
        $this->integrationHelper = $integrationHelper;
        $this->recombeeModel     = $recombeeModel;
        $this->translator        = $translator;
        $this->security          = $security;
        $this->entityManager     = $entityManager;
    }

    /**
     * @return string
     */
    public function getRecombeeRegex()
    {
        return $this->recombeeRegex;
    }

    /**
     * @return array
     */
    public function getRecombeeEvents()
    {
        $q = $this->entityManager->getConnection()->createQueryBuilder();

        $q->select('e.id, e.name, e.type, e.campaign_id, e.channel, e.channel_id as channelId')
            ->from(MAUTIC_TABLE_PREFIX.'campaign_events', 'e')
            ->where(
                $q->expr()->like('e.type', ':type')
            )
            ->setParameter('type', "recombee%")
            ->orderBy('e.id', 'DESC');

        return $q->execute()->fetchAll();
    }


}
