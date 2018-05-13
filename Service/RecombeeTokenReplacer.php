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

    public function __construct(
        RecombeeToken $recombeeToken,
        RecombeeTokenFinder $recombeeTokenFinder,
        RecombeeGenerator $recombeeGenerator
    ) {
        $this->recombeeToken       = $recombeeToken;
        $this->recombeeTokenFinder = $recombeeTokenFinder;
        $this->recombeeGenerator   = $recombeeGenerator;
    }

    public function replacePageTokens($content)
    {
        return $this->replaceTokensFromContent($content, 'pageTemplate');
    }

    public function replaceEmailTokens($content)
    {
        return $this->replaceTokensFromContent($content, 'emailTemplate');
    }

    /**
     * @param string $content
     */
    public function replaceTokensFromContent($content, $template)
    {
        $tokens = $this->recombeeTokenFinder->findTokens($content);
        if (!empty($tokens)) {
            foreach ($tokens as $key => $token) {
                $tokenContent = $this->recombeeGenerator->getContentByToken($token, $template);
                $content      = str_replace($key, $tokenContent, $content);
            }
        }

        return $content;
    }

}

