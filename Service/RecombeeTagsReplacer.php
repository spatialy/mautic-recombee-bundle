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

class RecombeeTagsReplacer
{
    /**
     * @var RecombeeToken
     */
    private $recombeeToken;

    private $options;

    /**
     * @var RecombeeTokenReplacer
     */
    private $recombeeTokenReplacer;


    /**
     * RecombeeTagsReplacer constructor.
     *
     * @param RecombeeTokenReplacer $recombeeTokenReplacer
     * @param RecombeeToken         $recombeeToken
     * @param array                 $options
     */
    public function __construct(RecombeeTokenReplacer $recombeeTokenReplacer, RecombeeToken $recombeeToken, $options = [])
    {
        $this->recombeeToken = $recombeeToken;
        $this->options = $options;
        $this->recombeeTokenReplacer = $recombeeTokenReplacer;
    }

    /**
     * @param $content
     *
     * @return string
     */
    public function replaceTags($content)
    {
        return $this->recombeeTokenReplacer->replaceTagsFromContent($content, $this->recombeeToken, $this->options);
    }

}

