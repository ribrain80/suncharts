<?php
/**
 * DAO Class file
 */

namespace Sunchemical\Suncharts\Dao;

use Sunchemical\Suncharts\Response\ServiceResponse;

/**
 * Database Access Object
 *
 * @author Riccardo Brambilla aka ribrain <riccardobra@gmail.com> <Riccardo.Brambilla@nttdata.com>
 * @copyright Sunchemical
 * @version 1.0
 * @package Suncharts
 *
 */
class DAO {
    
    /**
     * Connection Handle
     *
     * @var resource
     */
    private $linkD;

    /**
     * Logger
     * @var Logger
     */
    private $logger;

    /**
     * Queries params
     * @var array
     */
    private $params;
    
    /**
     * Instantiates the class and connect to remote DB
     * @param DAOConnectionInterface $conn DB connection
     * @param \Monolog\Logger $logger Logger instance
     */
    public function __construct( DAOConnectionInterface $conn, \Monolog\Logger $logger, $params ) {
        
        // # Get connection handle from DaoConnection singleton
        $this->logger = $logger;
        $this->linkD = @ $conn->getLinkD();
        if( !$this->linkD ) {
            throw new \Exception( "unable to connect to db host" );
        }
        $this->params = $params;
    }
    
    /**
     * Get work load by customer
     * @param int $code analysis ID
     * @return array the recordset
     */
    public function getLabWorkloadByoID( $y, $oTypeID, $oID, $countryID ) {

        // # Response container init
        $Response = new ServiceResponse();

        // # Check Handle
        if( !$this->linkD ) {
            return $Response;
        }
        
        // # Response container init
        $data = array();

        try {

            // # Execute query
            $RS = $this->linkD->Execute("

                    SET NOCOUNT ON;

                    DECLARE @TAB TABLE([Month] nvarchar(3), [WorkedHours] float)
                    INSERT INTO @TAB SELECT 'Jan' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 1,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Feb' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 2,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Mar' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 3,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Apr' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 4,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'May' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 5,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Jun' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 6,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Jul' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 7,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Aug' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 8,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Sep' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 9,  '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Oct' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 10, '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Nov' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 11, '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
                    INSERT INTO @TAB SELECT 'Dec' AS [Month], [dbo].[GetLabWorkloadByCustomer]( {$y}, 12, '{$oTypeID}', '{$oID}', '{$countryID}') AS WorkedHours
 
                    SELECT * FROM @TAB;

            "); 

            // # Check if something went wrong
            if( !$RS ) {
                throw new \Exception( "Unable to retrieve data for getLabWorkloadByCustomer!" );
            }

            // # retrieveing data and building response
            while( !$RS->EOF ) {
                
                $data[] = $RS->fields;
                $RS->MoveNext();
            }
            
            // # Check if something went wrong
            if( sizeOf( $data ) == 0 ) {
                throw new \Exception( "No data to retrieve for getLabWorkloadByCustomer!" );
            }

            // # Return response
            $Response->status = ServiceResponse::STATUS_OK;
            $Response->data   = $data;

        } catch( \Exception $e ) {

            $this->logger->addError( $e->getMessage() );
        }

        return $Response;
    }

    /**
     * [getLabWorkloadByTechnician description]
     * @return [type] [description]
     */
    public function getLabWorkloadByTechnician( $y, $oID, $countryID ) {

        // # Response container init
        $Response = new ServiceResponse();

        // # Check Handle
        if( !$this->linkD ) {
            return $Response;
        }
        
        // # Response container init
        $data = array();

        try {

            // # Execute query
            $RS = $this->linkD->Execute("

                    SET NOCOUNT ON;
                    DECLARE @MAX_H_MONTH INT
                    SET @MAX_H_MONTH = 168

                    DECLARE @TAB TABLE
                        (
                          [Month] NVARCHAR(3) ,
                          [Total] FLOAT ,
                          [MD] FLOAT ,
                          [AP] FLOAT ,
                          [PA] FLOAT ,
                          [HM] INT
                        )
                   
                    INSERT  INTO @TAB
                            SELECT  'Jan' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 1, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 1, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 1, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 1, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Feb' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 2, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 2, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 2, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 2, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Mar' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 3, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 3, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 3, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 3, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Apr' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 4, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 4, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 4, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 4, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'May' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 5, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 5, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 5, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 5, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Jun' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 6, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 6, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 6, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 6, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Jul' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 7, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 7, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 7, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 7, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Aug' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 8, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 8, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 8, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 8, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Sep' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 9, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 9, '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 9, '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 9, '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Oct' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 10, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 10,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 10,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 10,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Nov' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 11, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 11,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 11,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 11,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]
                    INSERT  INTO @TAB
                            SELECT  'Dec' AS [Month] ,
                                    [dbo].[GetLabWorkloadByTechnician]({$y}, 12, '{$oID}',
                                                                       '{$countryID}') AS [Total] ,
                                    [dbo].[GetLabWorkloadByTechnicianMD]({$y}, 12,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Material Development] ,
                                    [dbo].[GetLabWorkloadByTechnicianAP]({$y}, 12,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Analysis Project] ,
                                    [dbo].[GetLabWorkloadByTechnicianPA]({$y}, 12,
                                                                         '{$oID}',
                                                                         '{$countryID}') AS [Personal Activities] ,
                                    @MAX_H_MONTH AS [Max Hours per Month]

                    SELECT * FROM @TAB       
                "); 

                // # Check if something went wrong
                if( !$RS ) {
                    throw new \Exception( "Unable to retrieve data for getLabWorkloadByTechnician!" );
                }

                while( !$RS->EOF ) {
                    
                    $data[] = $RS->fields;
                    $RS->MoveNext();
                }    

                $Response->status = ServiceResponse::STATUS_OK;
                $Response->data   = $data;

            } catch( \Exception $e ) {

                $this->logger->addError( $e->getMessage() );
            }

            return $Response;        
    }

    public function getLabWorkloadByMonth ( $y, $m, $countryID ) {

        // # Response container init
        $Response = new ServiceResponse();

        // # Check Handle
        if( !$this->linkD ) {
            return $Response;
        }

        // # Response container init
        $data = array();

        try {

            // # Execute query
            $RS = $this->linkD->Execute("

                    SET NOCOUNT ON;
                    BEGIN
                        DECLARE @MAX_H_MONTH INT
                        SET @MAX_H_MONTH = 168
                        DECLARE @TAB TABLE
                            (
                                [Technician] NVARCHAR(50) ,
                                [MD] FLOAT ,
                                [AP] FLOAT ,
                                [PA] FLOAT ,
                                [HM] INT
                            )
                        DECLARE @UserId NVARCHAR(50)

                        DECLARE User_cursor CURSOR
                        FOR
                            SELECT [dbo].[User].[UserId]
                            FROM [dbo].[User]
                            WHERE [dbo].[User].[Type] = 'L'
                                AND [dbo].[User].[CountryId] = '{$countryID}'
                                AND [dbo].[User].[Active] = 1
                                AND [dbo].[User].[IncludedInLaboWorkload] = 1

                        OPEN User_cursor

                        FETCH NEXT FROM User_cursor
                            INTO @UserId

                        WHILE @@FETCH_STATUS = 0
                            BEGIN
                                INSERT INTO @TAB
                                    SELECT @UserId AS [Technician] ,
                                            [dbo].[GetLabWorkloadByTechnicianMD]({$y}, {$month}, @UserId, '{$countryID}') AS [Material Development] ,
                                            [dbo].[GetLabWorkloadByTechnicianAP]({$y}, {$month}, @UserId, '{$countryID}') AS [Analysis Project] ,
                                            [dbo].[GetLabWorkloadByTechnicianPA]({$y}, {$month}, @UserId, '{$countryID}') AS [Personal Activities] ,
                                            @MAX_H_MONTH AS [Max Hours per Month]
                                FETCH NEXT FROM User_cursor
                                    INTO @UserId
                            END
                        END

                        CLOSE User_cursor

                        DEALLOCATE User_cursor

                        DELETE FROM @TAB
                        WHERE  [MD] = 0
                            AND [AP] = 0
                            AND [PA] = 0

                        SELECT * FROM @TAB;        
                "); 
                
                // # Check if something went wrong
                if( !$RS ) {
                    throw new \Exception( "Unable to retrieve data for getLabWorkloadByMonth!" );
                }

                while( !$RS->EOF ) {
                    
                    $data[] = $RS->fields;
                    $RS->MoveNext();
                }     

                $Response->status = ServiceResponse::STATUS_OK;
                $Response->data   = $data;                

            } catch( \Exception $e ) {

                $this->logger->addError( $e->getMessage() );
            }

            return $Response;

    }

    /**
     * Gets technicians list
     * @return ServiceResponse
     */
    public function getTechnicians() {

        // # Response container init
        $Response = new ServiceResponse();

        // # Check Handle
        if( !$this->linkD ) {
            return $Response;
        }

       // # Response container init
        $data = array();
        try {

            $data = array();
            // # Execute query
            $RS = $this->linkD->Execute("

                SELECT  [dbo].[User].[UserId]
                FROM    [dbo].[User]
                WHERE   [dbo].[User].[Type] = 'L'
                    AND [dbo].[User].CountryId = 'IT'
                    AND [dbo].[User].[Active] = 1
                    AND [dbo].[User].[IncludedInLaboWorkload] = 1;        
            ");  


            // # Check if something went wrong
            if( !$RS ) {
                throw new \Exception( "Unable to retrieve data for getTechnicians!" );
            }   

            while( !$RS->EOF ) {
                
                $data[] = $RS->fields;
                $RS->MoveNext();
            }  

            $Response->status = ServiceResponse::STATUS_OK;
            $Response->data   = $data;            

        } catch( \Exception $e ) {

            $this->logger->addError( $e->getMessage() );
        }

        return $Response; 
    }

    /**
     * Gets customers list
     * @return ServiceResponse
     */    
    public function getOIDCustomer() {

        // # Response container init
        $Response = new ServiceResponse();
        $Response->data = array();

        // # Check Handle
        if( !$this->linkD ) {
            return $Response;
        }

        // # Response container init
        $data = array();

        try {

            // # Execute query (CUSTOMER)
            $RS = $this->linkD->Execute("

                SELECT  [CustomerCode] ,
                        [CustomerName]
                FROM    [LAM].[dbo].[Customer]
                WHERE   CountryScope = 'IT'
                        AND ( [CustomerCode] IN (
                              SELECT DISTINCT
                                        dbo.MaterialDevelopment.RequestorId
                              FROM      dbo.MaterialDevelopment
                              WHERE     dbo.MaterialDevelopment.RequestorTypeId = 1 )
                              OR [CustomerCode] IN (
                              SELECT DISTINCT
                                        dbo.AnalysisProject.OriginId
                              FROM      dbo.AnalysisProject
                              WHERE     dbo.AnalysisProject.OriginTypeId = 1 )
                            )     
            ");  


            // # Check if something went wrong
            if( !$RS ) {
                throw new \Exception( "Unable to retrieve data for getOIDCustomer!" );
            }  

            while( !$RS->EOF ) {
                
                $data[] = $RS->fields;
                $RS->MoveNext();
            }  

            $Response->status = ServiceResponse::STATUS_OK;
            $Response->data   = $data;            

        } catch( \Exception $e ) {

            $this->logger->addError( $e->getMessage() );
        }

        return $Response;         
    }

    /**
     * Gets vendors list
     * @return ServiceResponse
     */
    public function getOIDVendor() {

        // # Response container init
        $Response = new ServiceResponse();
        $Response->data = array();

        // # Check Handle
        if( !$this->linkD ) {
            return $Response;
        }

        // # Response container init
        $data = array();

        try {

            // # Execute query (VENDOR)
            $RS = $this->linkD->Execute("

               SELECT  [VendorCode] ,
                    [VendorName]
                FROM    [dbo].[Vendor]
                WHERE   CountryScope = 'IT'
                        AND ( [VendorCode] IN (
                              SELECT DISTINCT
                                        dbo.MaterialDevelopment.RequestorId
                              FROM      dbo.MaterialDevelopment
                              WHERE     dbo.MaterialDevelopment.RequestorTypeId = 2 )
                              OR [VendorCode] IN (
                              SELECT DISTINCT
                                        dbo.AnalysisProject.OriginId
                              FROM      dbo.AnalysisProject
                              WHERE     dbo.AnalysisProject.OriginTypeId = 2 )
                            )
            ");  

            // # Check if something went wrong
            if( !$RS ) {
                throw new \Exception( "Unable to retrieve data for getOIDVendor!" );
            }  

            while( !$RS->EOF ) {
                
                $data[] = $RS->fields;
                $RS->MoveNext();
            }  

            $Response->status = ServiceResponse::STATUS_OK;
            $Response->data   = $data;               

        } catch( \Exception $e ) {

            $this->logger->addError( $e->getMessage() );
        }

        return $Response;         
    }    

    /**
     * Destructor, cleans up resources
     */
    public function __destruct() {
        
        // # Free resource
        if( null != $this->linkD ) {
            
            // # Close handle
            $this->linkD->Close();
        }
    }
}
?>