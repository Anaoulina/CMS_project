<?php

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}

if(!class_exists('WC_Product_CSV_Importer_Controller')){
    include_once WC_ABSPATH . 'includes/admin/importers/class-wc-product-csv-importer-controller.php';
}

class LaStudio_Importer_WC extends WC_Product_CSV_Importer_Controller{
    public function get_mappings($file){
        $args = array(
            'lines'     => 1,
            'delimiter' => $this->delimiter,
        );

        $importer     = self::get_importer( $file, $args );
        $headers      = $importer->get_raw_keys();
        $mapped_items = $this->auto_map_columns( $headers );

        $map_from = [];
        $map_to = [];

        foreach ( $headers as $index => $name ){
            $mapped_value = $mapped_items[ $index ];
            $map_from[$index] = $name;
            $map_to[$index] = $mapped_value;
        }

        return [
            'from' => $map_from,
            'to' => $map_to,
        ];
    }
}