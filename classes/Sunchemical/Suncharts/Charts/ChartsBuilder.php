<?php
namespace Sunchemical\Suncharts\Charts;

use Monolog\Logger;
use CpChart\Services\pChartFactory;
use Sunchemical\Suncharts\Core\PropertyManager;

class ChartsBuilder extends pChartFactory {
    
    /**
     * PropertyManager injected
     * @var PropertyManager
     */
    private $PM;
    
    /**
     * Logger instance
     * @var Logger
     */
    private $log;
    
    /**
     * Constructor override
     * @param PropertyManager $PM [description]
     */
    public function __construct( PropertyManager $PM, Logger $log ) {
        $this->PM = $PM;
        $this->log = $log;
    }
    
    /**
     * Clears Up the images folder
     * Old files are unlinked
     */
    public function clearPast() {
        
        // # DirecotryIterator on image folder
        $chartsIterator = new \DirectoryIterator( $this->PM->getProperty( "basePath" ) . "/imagedata/" );
        $isDirEmpty = !$chartsIterator->valid();
         // # Check if it is already empty
        
        if( !$isDirEmpty ) {
            
            $this->log->error( "Unlinking old files i've found" );
            
            // # Unlink 'em all
            foreach( $chartsIterator as $file ) {
                if( $file->isFile() ) {
                    @unlink( $this->PM->getProperty( "basePath" ) . "/imagedata/" . $file->getFilename() );
                }
            }
        }
    }
    
    /**
     * Unlinks just one of the old images
     * @param $kind image id
     */    
    public function clearOne( $kind ) {
        @unlink( $this->PM->getProperty( "basePath" ) . "/imagedata/lab_effort_by_" . $kind . ".png" );
    }
    
    /**
     * Builds up the byoID chart
     * @param  array $byoIDData recordset
     * @return void
     */
    public function buildChartByoID( $byoIDData ) {
        
        try {
            
            // # data init
            $v = array();
            $m = array();
            
            // # Building up data series
            foreach( $byoIDData->data as $k => $record ) {
                $m[] = $record["Month"];
                $v[] = $record["WorkedHours"];
            }
            
            $totalcount = array_sum( $v );
            $cost = $totalcount * 35;
            
            $chartData = $this->newData( $v, "bycustomer" );
            $chartData->Antialias = true;
            
            $chartData->setSerieDescription( "By Customer", "By Customer" );
            $chartData->setSerieOnAxis( "bycustomer", 0 );
            $serieSettings = array(
                "R" => 229,
                "G" => 11,
                "B" => 11,
                "Alpha" => 80
            );
            $chartData->setPalette( "bycustomer", $serieSettings );
            
            $chartData->addPoints( $m, "Absissa" );
            $chartData->setAbscissa( "Absissa" );
            
            $chartData->setAxisPosition( 0, AXIS_POSITION_LEFT );
            $chartData->setAxisUnit( 0, "" );
            
            $myPicture = $this->newImage( 900, 300, $chartData );
            $Settings = array(
                "R" => 170,
                "G" => 183,
                "B" => 87,
                "Dash" => 1,
                "DashR" => 190,
                "DashG" => 203,
                "DashB" => 107
            );
            $myPicture->drawFilledRectangle( 0, 0, 900, 300, $Settings );
            
            $Settings = array(
                "StartR" => 219,
                "StartG" => 231,
                "StartB" => 139,
                "EndR" => 1,
                "EndG" => 138,
                "EndB" => 68,
                "Alpha" => 50
            );
            $myPicture->drawGradientArea( 0, 0, 900, 300, DIRECTION_VERTICAL, $Settings );
            
            $myPicture->drawRectangle( 0, 0, 899, 299, array(
                "R" => 0,
                "G" => 0,
                "B" => 0
            ) );
            
            $myPicture->setShadow( true, array(
                "X" => 1,
                "Y" => 1,
                "R" => 50,
                "G" => 50,
                "B" => 50,
                "Alpha" => 20
            ) );
            
            $myPicture->setFontProperties( array(
                "FontName" => "calibri.ttf",
                "FontSize" => 10
            ) );
            
            $TextSettings = array(
                "Align" => TEXT_ALIGN_MIDDLEMIDDLE,
                "R" => 255,
                "G" => 255,
                "B" => 255
            );
            $myPicture->drawText( 450, 35, "Effort Laboratorio per mese", $TextSettings );
            
            $myPicture->setShadow( false );
            $myPicture->setGraphArea( 50, 50, 875, 250 );
            
            $Settings = array(
                "Pos" => SCALE_POS_LEFTRIGHT,
                "Mode" => SCALE_MODE_FLOATING,
                "LabelingMethod" => LABELING_ALL,
                "GridR" => 255,
                "GridG" => 255,
                "GridB" => 255,
                "GridAlpha" => 50,
                "TickR" => 0,
                "TickG" => 0,
                "TickB" => 0,
                "TickAlpha" => 50,
                "LabelRotation" => 0,
                "CycleBackground" => 1,
                "DrawXLines" => 1,
                "DrawSubTicks" => 1,
                "SubTickR" => 255,
                "SubTickG" => 0,
                "SubTickB" => 0,
                "SubTickAlpha" => 50,
                "DrawYLines" => ALL,
                "Mode" => SCALE_MODE_ADDALL_START0
            );
            
            $myPicture->drawScale( $Settings );
            $myPicture->setShadow( true, array(
                "X" => 1,
                "Y" => 1,
                "R" => 20,
                "G" => 20,
                "B" => 20
            ) );
            
            $Config = array(
                "DisplayValues" => 1,
                "AroundZero" => 1,
                "DisplayValues" => true,
                "DisplayR" => 0,
                "DisplayB" => 0,
                "DisplayG" => 0
            );
            $myPicture->drawBarChart( $Config );
            
            $myPicture->drawText( 600, 40, "Total Hours/Cost: " . $totalcount . " ( " . $cost . " )" );
            
            $myPicture->render( $this->PM->getProperty( "basePath" ) . "/imagedata/lab_effort_by_customer.png" );
        
        } catch( \Exception $ex ) {
            echo 'There was an error: ' . $ex->getMessage();
        }
    }
    
    /**
     * Builds up the byoID chart
     * @param  array $byoIDData recordset
     * @return void
     */    
    public function buildChartByTechnician( $byTechnicianData ) {
        
        try {
            
            $months = array();
            $total = array();
            $MD = array();
            $AP = array();
            $PA = array();
            foreach( $rs1->data as $k => $record ) {
                $months[] = $record["Month"];
                $total[] = $record["Total"];
                $MD[] = $record["MD"];
                $AP[] = $record["AP"];
                $PA[] = $record["PA"];
            }
            
            $totalcount = array_sum( $total );
            $cost = $totalcount * 35;
            
            $chartData = $this->newData();
            
            $chartData->addPoints( $total, "Total" );
            $chartData->addPoints( $MD, "Mat Dev" );
            $chartData->addPoints( $AP, "An Proj" );
            $chartData->addPoints( $PA, "Pers Act" );
            
            $serieSettings = array(
                "R" => 154,
                "G" => 144,
                "B" => 83
            );
            $chartData->setPalette( "Total", $serieSettings );
            
            $chartData->setAxisName( 50, "Ore Lavorate" );
            $chartData->addPoints( $months, "Labels" );
            $chartData->setSerieDescription( "Labels", "Months" );
            $chartData->setAbscissa( "Labels" );
            $chartData->Antialias = true;
            
            /* Create the pChart object */
            $myPicture = $this->newImage( 1400, 600, $chartData );
            $myPicture->setFontProperties( array(
                "FontName" => "calibri.ttf",
                "FontSize" => 12
            ) );
            
            /* Draw the background */
            $Settings = array(
                "StartR" => 227,
                "StartG" => 227,
                "StartB" => 227,
                "EndR" => 220,
                "EndG" => 220,
                "EndB" => 220,
                "Alpha" => 60
            );
            $myPicture->drawFilledRectangle( 0, 0, 1400, 600, $Settings );
            
            /* Overlay with a gradient */
            $Settings = array(
                "StartR" => 240,
                "StartG" => 240,
                "StartB" => 240,
                "EndR" => 180,
                "EndG" => 180,
                "EndB" => 180,
                "Alpha" => 20
            );
            
            //$myPicture->drawGradientArea(0,0,1600,1000,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
            //$myPicture->drawGradientArea(0,0,1600,1000,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));
            
            $myPicture->drawGradientArea( 0, 0, 1400, 600, DIRECTION_VERTICAL, $Settings );
            
            $TextSettings = array(
                "Align" => TEXT_ALIGN_MIDDLEMIDDLE,
                "R" => 255,
                "G" => 255,
                "B" => 255
            );
            $myPicture->drawText( 700, 30, "Effort Laboratorio per Mese", $TextSettings );
            
            /* Draw the scale and the 1st chart */
            $myPicture->setGraphArea( 50, 50, 1300, 550 );
            $Settings = array(
                "Pos" => SCALE_POS_LEFTRIGHT,
                "Mode" => SCALE_MODE_FLOATING,
                "LabelingMethod" => LABELING_ALL,
                "GridR" => 227,
                "GridG" => 227,
                "GridB" => 227,
                "GridAlpha" => 50,
                "TickR" => 0,
                "TickG" => 0,
                "TickB" => 0,
                "TickAlpha" => 50,
                "LabelRotation" => 0,
                "CycleBackground" => 1,
                "DrawXLines" => 1,
                "DrawSubTicks" => 1,
                "SubTickR" => 255,
                "SubTickG" => 0,
                "SubTickB" => 0,
                "SubTickAlpha" => 50,
                "DrawYLines" => ALL,
                "Mode" => SCALE_MODE_ADDALL_START0
            );
            $myPicture->setShadow( true, array(
                "X" => 1,
                "Y" => 1,
                "R" => 0,
                "G" => 0,
                "B" => 0,
                "Alpha" => 10
            ) );
            
            $myPicture->drawScale( $Settings );
            $myPicture->setFontProperties( array(
                "FontName" => "calibri.ttf",
                "FontSize" => 8
            ) );
            
            //$myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
            $myPicture->drawBarChart( array(
                "DisplayPos" => LABEL_POS_INSIDE,
                "DisplayValues" => true,
                "DisplayR" => 0,
                "DisplayB" => 0,
                "DisplayG" => 0,
                "Rounded" => true,
                "Surrounding" => 30
            ) );
            
            // $myPicture->setShadow(FALSE);
            
            $myPicture->setFontProperties( array(
                "FontSize" => 14
            ) );
            
            $myPicture->drawText( 100, 24, "Total Hours/Cost: " . $totalcount . " ( " . $cost . " )" );
            $myPicture->drawThreshold( 168, array(
                "Alpha" => 80,
                "Ticks" => 2,
                "R" => 0,
                "G" => 0,
                "B" => 0
            ) );
            
            /* Write the chart legend */
            $myPicture->drawLegend( 1000, 15, array(
                "Style" => LEGEND_NOBORDER,
                "Mode" => LEGEND_HORIZONTAL
            ) );
            $myPicture->render( $this->PM->getProperty( "basePath" ) . "/imagedata/lab_effort_by_technician.png" );
        }
        catch( \Exception $ex ) {
            echo 'There was an error: ' . $ex->getMessage();
        }
    }
    
    /**
     * Builds up the byoID chart
     * @param  array $byoIDData recordset
     * @return void
     */    
    public function buildChartByMonth( $byMonthData ) {
        
        $technicians = array();
        $MD = array();
        $AP = array();
        $PA = array();
        foreach( $rs1->data as $k => $record ) {
            $technicians[] = $record["Technician"];
            $MD[] = $record["MD"];
            $AP[] = $record["AP"];
            $PA[] = $record["PA"];
        }
        
        $totalcount = array_sum( $MD ) + array_sum( $AP ) + array_sum( $PA );
        $cost = $totalcount * 35;
        
        /* Create and populate the pData object */
        $chartData = $this->newData();
        $chartData->addPoints( $MD, "Mat. Dev." );
        $chartData->addPoints( $AP, "An. Pro." );
        $chartData->addPoints( $PA, "Pers. Act." );
        $chartData->addPoints( $technicians, "Labels" );
        $chartData->setSerieDescription( "Labels", "Technicians" );
        $chartData->setAbscissa( "Labels" );
        $chartData->Antialias = true;
        
        /* Create the pChart object */
        $myPicture = $this->newImage( 1600, 1000, $chartData );
        $myPicture->setFontProperties( array(
            "FontName" => "calibri.ttf",
            "FontSize" => 12
        ) );
        $myPicture->drawGradientArea( 0, 0, 1600, 1000, DIRECTION_VERTICAL, array(
            "StartR" => 240,
            "StartG" => 240,
            "StartB" => 240,
            "EndR" => 180,
            "EndG" => 180,
            "EndB" => 180,
            "Alpha" => 100
        ) );
        $myPicture->drawGradientArea( 0, 0, 1600, 1000, DIRECTION_HORIZONTAL, array(
            "StartR" => 240,
            "StartG" => 240,
            "StartB" => 240,
            "EndR" => 180,
            "EndG" => 180,
            "EndB" => 180,
            "Alpha" => 20
        ) );
        
        /* Set the default font properties */
        $myPicture->setFontProperties( array(
            "FontName" => "verdana.ttf",
            "FontSize" => 10
        ) );
        
        /* Draw the scale and the chart */
        $myPicture->setGraphArea( 60, 20, 1500, 800 );
        
        $Settings = array(
            "Pos" => SCALE_POS_LEFTRIGHT,
            "Mode" => SCALE_MODE_FLOATING,
            "LabelingMethod" => LABELING_ALL,
            "GridR" => 255,
            "GridG" => 255,
            "GridB" => 255,
            "GridAlpha" => 50,
            "TickR" => 0,
            "TickG" => 0,
            "TickB" => 0,
            "TickAlpha" => 50,
            "LabelRotation" => 90,
            "CycleBackground" => 1,
            "DrawXLines" => 1,
            "DrawSubTicks" => 1,
            "SubTickR" => 255,
            "SubTickG" => 0,
            "SubTickB" => 0,
            "SubTickAlpha" => 50,
            "DrawYLines" => ALL,
            "Mode" => SCALE_MODE_ADDALL_START0,
            "DisplayPos" => LABEL_POS_INSIDE,
            "DisplayValues" => true,
            "DisplayR" => 0,
            "DisplayB" => 0,
            "DisplayG" => 0
        );
        $myPicture->setShadow( true, array(
            "X" => 1,
            "Y" => 1,
            "R" => 0,
            "G" => 0,
            "B" => 0,
            "Alpha" => 10
        ) );
        
        $myPicture->drawScale( $Settings );
        $myPicture->drawStackedBarChart( array(
            "DisplayPos" => LABEL_POS_INSIDE,
            "DisplayValues" => true,
            "DisplayR" => 0,
            "DisplayB" => 0,
            "DisplayG" => 0,
            "Surrounding" => 15,
            "InnerSurrounding" => 15
        ) );
        $myPicture->drawThreshold( 168, array(
            "Alpha" => 80,
            "Ticks" => 2,
            "R" => 0,
            "G" => 0,
            "B" => 0
        ) );
        
        $myPicture->setFontProperties( array(
            "FontSize" => 14
        ) );
        $myPicture->drawText( 100, 22, "Total Hours/Cost: " . $totalcount . " ( " . $cost . " )" );
        
        /* Write the chart legend */
        $myPicture->drawLegend( 1200, 10, array(
            "Style" => LEGEND_NOBORDER,
            "Mode" => LEGEND_HORIZONTAL
        ) );
        $myPicture->render( $this->PM->getProperty( "basePath" ) . "/imagedata/lab_effort_by_month.png" );
    }
}
