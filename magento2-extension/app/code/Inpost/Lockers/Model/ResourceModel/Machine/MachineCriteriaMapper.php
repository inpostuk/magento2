<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\ResourceModel\Machine;

use Magento\Framework\DB\GenericMapper;
use Inpost\Lockers\Model\ResourceModel\Machine;

/**
 * Class MachineCriteriaMapper
 * @package Inpost\Lockers\Model\ResourceModel\Machine
 */
class MachineCriteriaMapper extends GenericMapper
{
    const EARTH_RADIUS  = 3959;
    const KM_PER_DEGREE = 69.407;

    /**
     * Standard query builder initialization
     */
    protected function init()
    {
        $this->initResource(Machine::class);
    }

    /**
     * Build nearest coordinate filter
     *
     * @param array $coordinate
     * @param float $distance
     */
    public function mapNearestCoordinateFilter(array $coordinate, float $distance)
    {
        if (isset($coordinate['latitude'], $coordinate['longitude'])) {
            // Add 'distance' field expression to select
            $latitude = (float) $coordinate['latitude'];
            $longitude = (float) $coordinate['longitude'];

            $expression = '(' . static::EARTH_RADIUS . ' * ACOS(' .
                $this->connection->quoteInto('COS( RADIANS(?) ) * ', $latitude) .
                'COS( RADIANS( {{latitude}} ) ) * COS( RADIANS( {{longitude}} ) - ' .
                $this->connection->quoteInto('RADIANS(?) ) + ', $longitude) .
                $this->connection->quoteInto(
                    'SIN( RADIANS(?) ) * SIN( RADIANS( {{latitude}} ) ) ) )',
                    $latitude
                );
            $this->addExpressionFieldToSelect(
                'distance',
                $expression,
                ['latitude' => 'latitude', 'longitude' => 'longitude']
            );

            // Add filters to limit results by distance
            $latitudeDelta = $distance/static::KM_PER_DEGREE;
            $latitudeWhere = sprintf(
                'latitude BETWEEN (%s) AND (%s)',
                $latitude - $latitudeDelta,
                $latitude + $latitudeDelta
            );

            $radians = $this->connection->quoteInto(
                'RADIANS( ? ) ) * ' . static::KM_PER_DEGREE . ' ) ) )',
                $latitude
            );

            $longitudeDelta = $this->connection->quoteInto(
                '(? / ABS( COS( ' . $radians,
                $distance
            );
            $longitudeWhere = 'longitude BETWEEN ' .
                $this->connection->quoteInto('(? - ' . $longitudeDelta, $longitude) . ' AND ' .
                $this->connection->quoteInto('(? + ' . $longitudeDelta, $longitude);

            $this->getSelect()
                ->where($latitudeWhere)
                ->where($longitudeWhere)
                ->having('distance <= ?', $distance)
                ->order('distance');
        }
    }
}
