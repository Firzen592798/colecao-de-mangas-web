<?php
include("Secret.php");
class Database
{

    protected $connection = null;
 
    public function __construct()
    {
        $servername = "localhost";
        #LOCAL
        $username = "root";
        $password = "";
        $dbname = "colecao_mangas";
        #PRODUÇÃO
        //$username = USERNAME;
        //$password = PASSWORD;
        //$dbname = DBNAME;
        try {
            $this->connection = new mysqli($servername, $username, $password, $dbname);
            $this->connection->set_charset("utf8");
            if ( mysqli_connect_errno()) {
                var_dump(mysqli_connect_errno());
                throw new Exception("Could not connect to database.");   
            }
        } catch (Exception $e) {
            var_dump($e);
            throw new Exception($e->getMessage());   
        }           
    }
 
    public function select($query = "" , $params = [])
    {
        try {
            $stmt = $this->executeStatement( $query , $params );
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);               
            $stmt->close();
 
            return $result;
        } catch(Exception $e) {
            throw New Exception( $e->getMessage() );
        }
        return false;
    }
 
    protected function executeStatement($query = "" , $params = [])
    {
        try {
            $stmt = $this->connection->prepare( $query );
 
            if($stmt === false) {
                throw New Exception("Unable to do prepared statement: " . $query);
            }
 
            if( $params ) {
                $stmt->bind_param($params[0], $params[1]);
            }
 
            $stmt->execute();
            return $stmt;
        } catch(Exception $e) {
            throw New Exception( $e->getMessage() );
        }   
    }
}