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

use Mautic\PageBundle\Event\PageDisplayEvent;
use MauticPlugin\MauticRecombeeBundle\Service\RecombeeToken;



class RecombeeTokenFinder
{

    private $recombeeTokens = [];

    private $recombeeRegex = '{Recombee=(.*?)}';

    /**
     * @var \MauticPlugin\MauticRecombeeBundle\Service\RecombeeToken
     */
    private $recombeeToken;


    public function __construct(RecombeeToken $recombeeToken)
    {
        $this->recombeeToken = $recombeeToken;
    }


    public function findTokens($content)
    {
        $regex   = '/'.$this->recombeeRegex.'/i';
        preg_match_all($regex, $content, $matches);
        if (empty($matches[1])) {
            return;
        }
        foreach ($matches[1] as $key => $match) {
            $this->recombeeToken->parseToken($match);
            if ($this->recombeeToken->isIsToken()) {
                $this->recombeeTokens[$matches[0][$key]] = $this->recombeeToken;
            }
        }
        return $this->recombeeTokens;
    }

    /**
     * @return array
     */
    public function getRecombeeTokens()
    {
        return $this->recombeeTokens;
    }

    /**
     * @param array $recombeeTokens
     */
    public function setRecombeeTokens(array $recombeeTokens)
    {
        $this->recombeeTokens = $recombeeTokens;
    }


}

