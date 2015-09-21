<?php
/**
 * PropertyManager class file
 */

namespace Sunchemical\Suncharts\Core;

/**
 *
 * PropertyManager singleton class
 *
 * @author Riccardo Brambilla aka ribrain <riccardobra@gmail.com> <Riccardo.Brambilla@nttdata.com>
 * @copyright Sunchemical
 * @version 1.0
 * 
 */
class PropertyManager {
	
    /**
     * *.properties files path
     * 
     * @var string
     */
    private $filterPath;
    
    /**
     * Configuration path
     * 
     * @var string
     */
    private $confPath;
    
    /**
     * Properties loaded
     * 
     * @var array
     */
    private $properties;
    
    /**
     * Fatal error message
     *
     * @var string
     */
    const DEFAULT_ERROR_MSG = "FATAL ERROR, UNABLE TO LOAD CONFIGURATIONS";
 	
    /**
     * Private ( singleton ) constructor
     */
    public function __construct() { 
        $this->loadProperties();
    }

    /**
     * Loads all the properties in the $properties class member
     */
    public function loadProperties () {
    	
        // # Failover
    	$this->filterPath = "config/filters/";
    	$this->confPath   = "config/app_properties.xml";

        $xmlConf = @ simplexml_load_file( $this->confPath );
        if( false === $xmlConf ) {
            die ( self::DEFAULT_ERROR_MSG );
        }
	
        // # Init to default value
        $propertiesFilename = "local.filter.properties.php";

        // # Retrieve defined hosts list
        $hosts = @ $xmlConf->xpath( "//property[@type='host']" );
        if( false === $hosts ) {
        	die ( self::DEFAULT_ERROR_MSG );
        }
        
        // # Getting current host, www. is not considered
        $domain = str_replace( "www.", "", $_SERVER[ "HTTP_HOST" ] );
        
        // # Try to find the right one
        foreach( $hosts as $hkey => $hnode ) {
			
            if( null === $hnode || null === $hnode->attributes() ) {
                continue;
            }

            $hnodeAttrValue = $hnode->attributes()->value;
           
            if( empty( $hnodeAttrValue ) ) {
                continue;
            }

            if( $domain != $hnodeAttrValue ) {
                continue;
            }

            // # here it is
            $propertiesFilename = $hnode->attributes()->name . ".filter.properties.php";			
        }
        
        // # Load .properties content
        $appConf = @ parse_ini_file( $this->filterPath . $propertiesFilename );
        if( false === $appConf ){
            die ( self::DEFAULT_ERROR_MSG );
        }

        // # Get properties list
        $propertiesNodes = @ $xmlConf->xpath( "//property" );
        if( false === $propertiesNodes || sizeOf( $propertiesNodes ) === 0 ) {
            die ( self::DEFAULT_ERROR_MSG );
        }
	
        // # Merge
        foreach( $propertiesNodes as $p ) {
	
            $attr = $p->attributes();
	
            if( null === $attr || !is_object( $attr ) || sizeOf( $attr ) === 0 ){
               continue;
            } 
            
            $attrName = ( string ) $attr->name;
            if ( isset( $appConf[ $attrName ] ) ) { 
		    	$this->properties[ $attrName ] = $appConf[ $attrName ];
			}

        }

        // # Retrieve app version
        $appVersionNode = @ $xmlConf->xpath( "//property[@name='__app_version']" );
        if( $appVersionNode ) {
        	$this->properties[ "__app_version" ] = ( string ) $appVersionNode[0]->attributes()->value;
        }
    }

	/**
	 * Get loaded properties
	 * 
	 * @return array
	 */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * properties setter
     * @param array $properties the properties to be set
     */
    public function setProperties( $properties ) {
        $this->properties = $properties;
    }

    /**
     * Returns a single property's value
     * 
     * @param string $property
     * @return multitype:|NULL
     */
    public function getProperty( $property ) {
    	
        if ( in_array( $property, array_keys( $this->properties ) ) ) {
            return $this->properties[ $property ];
        }
        
        return null;
    }	
}
?>
