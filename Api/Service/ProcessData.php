<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecombeeBundle\Api\Service;

use Mautic\CoreBundle\Translation\Translator;
use MauticPlugin\MauticRecombeeBundle\Api\RecombeeApi;
use Psr\Log\LoggerInterface;
use Recombee\RecommApi\Requests as Reqs;
use Recombee\RecommApi\Exceptions as Ex;
use Recurr\Transformer\TranslatorInterface;

class ProcessData
{
    private $requestsPropertyName = [];

    private $requestsPropertyValues = [];

    /**
     * @param $items
     *
     * @return array
     */
    public function itemsToMultiArray($items)
    {
        if (is_array(end($items))) {
            return $items;
        } else {
            return [$items];
        }
    }

    /**
     * ProcessData constructor.
     *
     * @param array $items
     * @param       $funcProperty
     * @param       $funcValue
     */
    public function __construct(array $items, $funcProperty, $funcValue)
    {
        $funcProperty = 'Recombee\RecommApi\Requests\\'.$funcProperty;
        $funcValue    = 'Recombee\RecommApi\Requests\\'.$funcValue;
        $items        = $this->itemsToMultiArray($items);
        $uniqueParams = [];
        foreach ($items as $item) {
            /** @todo  check */
            if (!isset($item['id'])) {
                throw new \Exception('ID  missing: '.print_r($item, true));
            }
            $itemId = $item['id'];
            unset($item['id']);
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    if (count($value) == count($value, COUNT_RECURSIVE)) {
                        $item[$key] = json_encode(array_values($value));
                        unset($item[$key]);
                    } else {
                        unset($item[$key]);
                        continue;
                    }
                }

                if (!isset($uniqueParams[$key]) || $uniqueParams[$key] != '') {
                    $uniqueParams[$key] = $value;
                }
                // convert date to timestamp
                if (is_string($value) && (bool) strtotime($value)) {
                    $item[$key] = strtotime($value);
                }
            }
            $this->requestsPropertyValues[] = new $funcValue($itemId, $item, ['cascadeCreate' => true]);
        }
        $allowedImagesFileTypes = ['gif', 'png', 'jpg'];
        foreach ($uniqueParams as $key => $value) {
            if (is_array($value)) {
                $this->requestsPropertyName[] = new $funcProperty($key, 'set');
            } else {
                if (in_array(pathinfo($value, PATHINFO_EXTENSION), $allowedImagesFileTypes)) {
                    $this->requestsPropertyName[] = new $funcProperty($key, 'image');
                } elseif (is_int($value)) {
                    $this->requestsPropertyName[] = new $funcProperty($key, 'int');
                } elseif (is_double($value)) {
                    $this->requestsPropertyName[] = new $funcProperty($key, 'double');
                } elseif (is_double($value)) {
                    $this->requestsPropertyName[] = new $funcProperty($key, 'double');
                } elseif (is_bool($value)) {
                    $this->requestsPropertyName[] = new $funcProperty($key, 'boolean');
                } elseif ((bool) strtotime($value) === true) {
                    $this->requestsPropertyName[] = new $funcProperty($key, 'timestamp');
                } else {
                    $this->requestsPropertyName[] = new $funcProperty($key, 'string');
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getRequestsPropertyName()
    {
        return $this->requestsPropertyName;
    }

    /**
     * @return array
     */
    public function getRequestsPropertyValues()
    {
        return $this->requestsPropertyValues;
    }
}

