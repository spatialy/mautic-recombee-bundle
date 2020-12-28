<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Service;

use Mautic\CoreBundle\Helper\TemplatingHelper;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticRecombeeBundle\Api\RecombeeApi;
use MauticPlugin\MauticRecombeeBundle\Api\Service\ApiCommands;
use MauticPlugin\MauticRecombeeBundle\Entity\Recombee;
use MauticPlugin\MauticRecombeeBundle\Model\RecombeeModel;
use Recombee\RecommApi\Exceptions as Ex;
use Recombee\RecommApi\Requests as Reqs;

class RecombeeGenerator
{
    /** @var RecombeeApi */
    private $recombeeApi;

    /**
     * @var RecombeeModel
     */
    private $recombeeModel;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var \Twig_Extension
     */
    private $twig;

    /**
     * @var ApiCommands
     */
    private $apiCommands;

    private $header;

    private $footer;

    /**
     * @var TemplatingHelper
     */
    private $templateHelper;

    /** @var array $items */
    private $items = [];

    /** @var array  */
    private $cache = [];

    /**
     * RecombeeGenerator constructor.
     *
     * @param RecombeeModel     $recombeeModel
     * @param RecombeeApi       $recombeeApi
     * @param LeadModel         $leadModel
     * @param \Twig_Environment $twig
     * @param ApiCommands       $apiCommands
     * @param TemplatingHelper  $templatingHelper
     */
    public function __construct(
        RecombeeModel $recombeeModel,
        RecombeeApi $recombeeApi,
        LeadModel $leadModel,
        \Twig_Environment $twig,
        ApiCommands $apiCommands,
        TemplatingHelper $templatingHelper
    ) {
        $this->recombeeApi    = $recombeeApi;
        $this->recombeeModel  = $recombeeModel;
        $this->leadModel      = $leadModel;
        $this->twig           = $twig;
        $this->apiCommands    = $apiCommands;
        $this->templateHelper = $templatingHelper;
    }

    /**
     * @param RecombeeToken $recombeeToken
     * @param array         $options
     */
    public function getResultByToken(RecombeeToken $recombeeToken, $options = [])
    {
        $hash = md5(\GuzzleHttp\json_encode($recombeeToken).\GuzzleHttp\json_encode($options));
        if (!empty($this->cache[$hash])) {
            return $this->cache[$hash];
        }

        $recombee = $this->recombeeModel->getEntity($recombeeToken->getId());

        if (!$recombee instanceof Recombee) {
            return;
        }

        //$options['filter']           = $recombee->getFilter();
        //$options['booster']          = $recombee->getBoost();
        $options['returnProperties'] = true;
        $recombeeToken->setAddOptions($options);
        try {
            switch ($recombeeToken->getType()) {
                case "RecommendItemsToUser":
                    $this->apiCommands->callCommand(
                        'RecommendItemsToUser',
                        $recombeeToken->getOptions(['userId', 'limit'])
                    );
                    $items = $this->apiCommands->getCommandOutput();
                    break;
            }
            $this->items = $items['recomms'];
            $this->cache[$hash] = $this->items;
            return $this->items;

        } catch (Ex\ApiTimeoutException $e) {
            die(print_r($e->getMessage()));
            //Handle timeout => use fallback
        } catch (Ex\ResponseException $e) {
            die(print_r($e->getMessage()));
            //Handle errorneous request => use fallback
        } catch (Ex\ApiException $e) {
            die(print_r($e->getMessage()));
            //ApiException is parent of both ResponseException and ApiTimeoutException
        }
    }

    /**
     * @param $content
     *
     * @return string
     */
    public function replaceTagsFromContent($content)
    {
        return $this->twig->createTemplate($content)->render($this->getFirstItem()['values']);
    }

    /**
     * @param RecombeeToken $recombeeToken
     *
     * @return string|void
     */
    public function getContentByToken(RecombeeToken $recombeeToken)
    {
        /** @var Recombee $recombee */
        $recombee = $this->recombeeModel->getEntity($recombeeToken->getId());

        if (!$recombee instanceof Recombee) {
            return;
        }

        $items = $this->getResultByToken($recombeeToken);
        if (empty($items)) {
            return;
        }
        if ($recombee->getTemplateType() == 'basic') {
            $headerTemplateCore = $this->templateHelper->getTemplating()->render(
                'MauticRecombeeBundle:Recombee:generator-header.html.php',
                [
                    'recombee' => $recombee,
                ]
            );
            $footerTemplateCore = $this->templateHelper->getTemplating()->render(
                'MauticRecombeeBundle:Recombee:generator-footer.html.php',
                [
                    'recombee' => $recombee,
                ]
            );
            $bodyTemplateCore   = $this->templateHelper->getTemplating()->render(
                'MauticRecombeeBundle:Recombee:generator-body.html.php',
                [
                    'recombee' => $recombee,
                ]
            );
            $headerTemplate = $this->twig->createTemplate($headerTemplateCore);
            $footerTemplate = $this->twig->createTemplate($footerTemplateCore);
            $bodyTemplate   = $this->twig->createTemplate($bodyTemplateCore);

        } else {
            $headerTemplate = $this->twig->createTemplate($recombee->getTemplate()['header']);
            $footerTemplate = $this->twig->createTemplate($recombee->getTemplate()['footer']);
            $bodyTemplate   = $this->twig->createTemplate($recombee->getTemplate()['body']);
        }

        return $this->getTemplateContent($headerTemplate, $footerTemplate, $bodyTemplate);
    }

    /**
     *
     * @return string
     */
    private function getTemplateContent($headerTemplate, $footerTemplate, $bodyTemplate)
    {
        $output = $headerTemplate->render($this->getFirstItem()['values']);
        foreach ($this->getItems() as $item) {
            $output .= $bodyTemplate->render($item['values']);
        }
        $output.= $footerTemplate->render($this->getFirstItem()['values']);
        return $output;
    }


    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return mixed
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $keys = $this->getItemsKeys();
        foreach ($this->items as &$item) {
            foreach ($item['values'] as $key => &$ite) {
                if (is_array($ite)) {
                    $ite = implode(', ', $ite);
                }
            }
            $item['values']['keys'] = $keys;
        }
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * Return new token keys with comma separated item IDs
     *
     * @param string $separator
     *
     * @return string
     */
    private function getItemsKeys($separator = ',')
    {
        return  implode($separator, array_column($this->items, 'id'));
    }

    /**
     * Get first item
     *
     * @return array
     */
    public function getFirstItem()
    {
        $items = $this->getItems();
        return current($items);
    }
}

