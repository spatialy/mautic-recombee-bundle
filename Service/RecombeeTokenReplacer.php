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

use Mautic\PageBundle\Model\TrackableModel;
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

    private $replacedTokens;

    /**
     * @var TrackableModel
     */
    private $trackableModel;

    /**
     * RecombeeTokenReplacer constructor.
     *
     * @param RecombeeToken       $recombeeToken
     * @param RecombeeTokenFinder $recombeeTokenFinder
     * @param RecombeeGenerator   $recombeeGenerator
     * @param TrackableModel      $trackableModel
     */
    public function __construct(
        RecombeeToken $recombeeToken,
        RecombeeTokenFinder $recombeeTokenFinder,
        RecombeeGenerator $recombeeGenerator,
        TrackableModel $trackableModel
    ) {
        $this->recombeeToken       = $recombeeToken;
        $this->recombeeTokenFinder = $recombeeTokenFinder;
        $this->recombeeGenerator   = $recombeeGenerator;
        $this->trackableModel = $trackableModel;
    }

    /**
     * @return RecombeeToken
     */
    public function getRecombeeToken()
    {
        return $this->recombeeToken;
    }

    /**
     * @return RecombeeGenerator
     */
    public function getRecombeeGenerator()
    {
        return $this->recombeeGenerator;
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
                    $content      = str_replace($key, $tokenContent, $content);
                    $this->replacedTokens[$key] = $tokenContent;
                }else{
                    // no content, no token
                    $content      = str_replace($key, '', $content);
                }
            }
        }

        return $content;
    }

    /**
     * @param               $content
     * @param RecombeeToken $recombeeToken
     * @param array $options
     */
    public function replaceTagsFromContent($content, RecombeeToken $recombeeToken, $options = [])
    {
        $this->recombeeGenerator->getResultByToken($recombeeToken, $options);
        return $this->recombeeGenerator->replaceTagsFromContent($content);
    }


    /**
     * @return boolean
     */
    public function hasItems()
    {
        if (!empty($this->replacedTokens)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getReplacedTokens()
    {
        return $this->replacedTokens;
    }

}

