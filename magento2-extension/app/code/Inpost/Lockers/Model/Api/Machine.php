<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\Api;

class Machine extends \Magento\Framework\DataObject
{
    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     * @return Varien_Object
     */
    public function addData(array $arr)
    {
        $arr = $this->prepareData($arr);
        foreach ($arr as $index => $value) {
            $this->setData($index, $value);
        }
        return $this;
    }

    private function prepareData(array $arr)
    {
        if (array_key_exists('_links', $arr)) {
            $links = (array)$arr['_links'];
            unset($arr['_links']);
            if (array_key_exists('self', $links)) {
                $arr['self'] = $links['self']->href;
            }
            if (array_key_exists('minimap', $links)) {
                $arr['minimap'] = $links['minimap']->href;
            }
        }
        if (array_key_exists('address', $arr)) {
            $address = (array)$arr['address'];
            unset($arr['address']);
            $arr = array_merge($arr, $address);
        }
        if (array_key_exists('location', $arr)) {
            if (count($arr['location']) == 2) {
                $arr['latitude'] = $arr['location'][0];
                $arr['longitude'] = $arr['location'][1];
                $arr['location'] = json_encode($arr['location']);
            }
        }
        if (array_key_exists('functions', $arr)) {
            $arr['functions'] = json_encode($arr['functions']);
        }
        return $arr;
    }
}
