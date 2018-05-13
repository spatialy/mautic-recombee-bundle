<?php

namespace MauticPlugin\MauticRecombeeBundle;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;
use Mautic\WebhookBundle\Entity\Event;
use Mautic\WebhookBundle\Entity\Webhook;
use Mautic\WebhookBundle\Model\WebhookModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MauticRecombeeBundle extends PluginBundleBase
{
    public static function onPluginInstall(Plugin $plugin, MauticFactory $factory, $metadata = null, $installedSchema = null)
    {
        /** @var WebhookModel $webhookModel */
        $recombeeHookUrl =$factory->getRouter()->generate('mautic_recombee_webhook',[],UrlGeneratorInterface::ABSOLUTE_URL);
        $webhookModel = $factory->getModel('webhook.webhook');
        $webhookEntity = $webhookModel->getRepository()->findOneBy(['webhookUrl'=> $recombeeHookUrl]);
        // create webhook if not exist
        if(!$webhookEntity instanceof Webhook){
            $webhookEntity = new Webhook();
            $webhookEntity->setName($factory->getTranslator()->trans('mautic.plugin.recombee.webhook'));
            $webhookEntity->setWebhookUrl($recombeeHookUrl);
            $events = [];
            $hooks = ['mautic.lead_post_save_new','mautic.lead_post_save_update', 'mautic.lead_post_delete'];
            foreach($hooks as $hook){
                $event = new Event();
                $event->setEventType($hook);
                $event->setWebhook($webhookEntity);
                $events[] = $event;
            }
            $webhookEntity->setEvents($events);
            $webhookModel->saveEntity($webhookEntity);
        }
    }
}
