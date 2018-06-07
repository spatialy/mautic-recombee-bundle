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

use Recombee\RecommApi\Exceptions as Ex;
use Recombee\RecommApi\Requests as Reqs;

class RecombeeTokenReplacer
{
    /**
     * @var RecombeeToken
     */
    private $recombeeToken;

    /**
     * @var RecombeeTokenFinder
     */
    private $recombeeTokenFinder;

    /**
     * @var RecombeeGenerator
     */
    private $recombeeGenerator;

    private $hasItems = false;

    public function __construct(
        RecombeeToken $recombeeToken,
        RecombeeTokenFinder $recombeeTokenFinder,
        RecombeeGenerator $recombeeGenerator
    ) {
        $this->recombeeToken       = $recombeeToken;
        $this->recombeeTokenFinder = $recombeeTokenFinder;
        $this->recombeeGenerator   = $recombeeGenerator;
    }

    /**
     * @param       $content
     * @param array $options
     *
     * @return mixed
     * @internal param $event
     */
    public function replaceTokensFromContent($content, $options = [])
    {
        $tokens = $this->recombeeTokenFinder->findTokens($content);
        if (!empty($tokens)) {
            /**
             * @var  $key
             * @var  RecombeeToken $token
             */
            foreach ($tokens as $key => $token) {
                $token->setAddOptions($options);
                $tokenContent = $this->recombeeGenerator->getContentByToken($token);
                if (!empty($tokenContent)) {
                    $this->setHasItems(true);
                    $content      = str_replace($key, $tokenContent, $content);
                }else{
                    // no content, no token
                    $content      = str_replace($key, '', $content);
                }
            }
        }

        return $content;
    }

    /**
     * @return boolean
     */
    public function isHasItems()
    {
        return $this->hasItems;
    }

    /**
     * @param boolean $hasItems
     */
    public function setHasItems($hasItems)
    {
        $this->hasItems = $hasItems;
    }

}

