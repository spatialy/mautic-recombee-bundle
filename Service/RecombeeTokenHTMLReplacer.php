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


class RecombeeTokenHTMLReplacer
{

    private $tokenValues = [];

    private $recombeeTokens = [];

    /**
     * @var RecombeeGenerator
     */
    private $recombeeGenerator;

    private $recombeeToken;


    /**
     * RecombeeTokenHTMLReplacer constructor.
     *
     * @param RecombeeGenerator $recombeeGenerator
     * @param RecombeeToken     $recombeeToken
     */
    public function __construct(RecombeeGenerator $recombeeGenerator, RecombeeToken $recombeeToken)
    {
        $this->recombeeGenerator = $recombeeGenerator;
        $this->recombeeToken     = $recombeeToken;
    }


    public function findTokens($content)
    {
        // replace slots
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);

        $divContent = $xpath->query('//*[@class="mautic-recombee"]');
        for ($i = 0; $i < $divContent->length; ++$i) {
            $recombeeBlock = $divContent->item($i);
            $this->recombeeToken->setToken($this->parseData($recombeeBlock));
            $newContent = $this->recombeeGenerator->getContentByToken($this->recombeeToken, $content);
            $newnode    = $dom->createDocumentFragment();
            $newnode->appendXML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
            // in case we want to just change the slot contents:
            // $slot->appendChild($newnode);
            $recombeeBlock->parentNode->replaceChild($newnode, $newContent);


        }
        $dom->saveHTML();
    }


    /**
     * @param \DOMElement $recombeeBlock
     */
    private function parseData(\DOMElement $recombeeBlock)
    {
        $tokenValues = [];
        foreach ((new RecombeeAttr())->getRecombeeAttr() as $attr) {
            if ($attribute = $recombeeBlock->getAttribute('data-'.$attr)) {
                $tokenValues[$attr] = $attribute;
            }
        }

        return $tokenValues;
    }


}

