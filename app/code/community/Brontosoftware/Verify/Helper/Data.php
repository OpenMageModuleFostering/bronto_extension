<?php

class Brontosoftware_Verify_Helper_Data extends Mage_Core_Helper_Abstract
{
    private static $_areas = array(
        'all' => 'All Areas',
        'models' => 'Only Models',
        'helpers' => 'Only Helpers',
        'blocks' => 'Only Blocks',
        'resources' => 'Only Resources'
    );

    /**
     * Gets the connector options
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = array();
        foreach (self::$_areas as $id => $name) {
            $options[] = array( 'id' => $id, 'name' => $this->__($name) );
        }
        return $options;
    }

    /**
     * Gets all of the select areas
     *
     * @param string $areaName
     * @return array
     */
    public function getSelectedAreas($areaName) {
        if ($areaName == 'all') {
            $areas = array_keys(self::$_areas);
            array_shift($areas);
            return $areas;
        } else {
            return array($areaName);
        }
    }
}
