<?php
class Database {

  public  $host;
  public  $database;
  public  $username;
  public  $password;
  private  $DBH;

  /**
   * Database Constructor
   */
  function __construct($host, $database, $username, $password) {
    $this->host = $host;
    $this->database = $database;
    $this->username = $username;
    $this->password = $password;

    $this->options = array(
      PDO::ATTR_PERSISTENT  => true,
      PDO::ATTR_ERRMODE     => PDO::ERRMODE_EXCEPTION
    );
  }

  /**
   * Connect to MySQL DB using PDO
   * @author Danny Grove <danny@drgrovellc.com>
    @version 1.0
   */
  public function makeMySQLConnection() {
    try {
      $this->DBH = new PDO("mysql:host=".$this->host.";dbname=".$this->database, $this->username, $this->password, $this->options);
      /* Set Error handling */
    } catch (PDOException $e) {
      echo  $e->getMessage();
    } catch(Exception $e) {
      echo $e->getMessage();
    }

  }

  /** 
   * Connect to MSSQL DB using PDO
   * @author Danny Grove <danny@drgrovellc.com>
   * @version 1.0
   */
  public function makeMSSQLConnection() {
    try {
      $this->DBH = new PDO("dblib:host=".$this->host.";dbname=".$this->database, $this->username, $this->password, array());
      /* Set Error handling */
    } catch (PDOException $e) {
      echo  $e->getMessage();
    } catch(Exception $e) {
      echo $e->getMessage();
    }
  }
  /**
   * Kills connection to Database using PDO
   * @author Danny Grove
   * @version 1.0
   */
  public  function killConn() {
    $this->DBH = null;
  }


  /**
   * Take in a query in string format and executes either a prepare or query method
   * @author Danny Grove
   * @version 1.5
   * @param $queryString, $data
   * @return $response of statement in a array
   */
  public  function query($queryString, $data=null) {
    if($data==null) {
      // If Data is Null
      try {
        $STH = $this->DBH->query($queryString); 
      } catch (PDOException $e) {
        echo  $e->getMessage();
      } catch (Exception $e) {
        echo $e->getMessage();
      } 

    } else {
      // If Data is not Null
      try {
        $STH = $this->DBH->prepare($queryString);
        $STH->execute($data);
      } catch (PDOException $e) {
        echo  $e->getMessage();
      } catch (Exception $e) {
        echo $e->getMessage();
      } 
    }

    // Fetch All and Return
    $response = $STH->fetchAll(PDO::FETCH_ASSOC);

    // Either Gives Rows or True (Successful Query)
    $response = ($response ? $response : true);
    return $response;
  }

  /**
   * Takes in an array and return an imploded string
   * @author Danny Grove
   * @version 1.0
   * @param $fields
   * @return $string of fields for use in a query
   */
  public  function condenseFields($fields) {
    $fields = implode(", ", $fields);
    return $fields;
  }

  /**
   * Takes a key value array and returns the condensed keys as fields
   * @author Danny Grove
   * @version 1.0
   * @param $fields
   * @return $string of fields for use in query
   */
  public function condenseKeys($fields) {
    $keys = array();
    foreach($fields as $key => $value) {
      array_push($keys, $key);
    }
    $string = implode(", ", $keys);
    return $string;
  }

  /** 
   * Takes a key => value array and builds set placeholders for update statements
   * @author Danny Grove
   * @version 1.0
   * @param $fields
   * @return string of set placeholders for use in query
   */
  public function condenseKeysWithPlaceholders($fields) {
    $keysWithPlaceholders = array();
    foreach($fields as $key => $value) {
      array_push($keysWithPlaceholders, "$key=?");
    }
    $string = implode(", ", $keysWithPlaceholders);
    return $string;
  }

  /**
   * Takes in an array of fields and returns an imploded string with placeholders added
   * added
   * @author Danny Grove
   * @version 1.0
   * @param $fields
   * @return $string of fields with placeholders for a query
   */
  public  function condenseWithPlaceholders($fields) {
    foreach($fields as $key => &$value) {
      $value .= "=?";
    }
    $fields = implode(", ", $fields);

    return $fields;
  }

  /**
   * Takes in an array of fields and returns a string of placeholders
   * @author Danny Grove
   * @version 1.0
   * @param $array
   * @return $string of placeholders
   */
  public  function buildPlaceholders($array) {
    $placeholders = "";

    foreach($array as $key => $value) {
      $placeholders .= "?, ";
    }

    $placeholders = substr($placeholders, 0, -2);

    return $placeholders;
  }
 
  /**
   * Takes in an array of fields and returns a string of named placeholders
   * @author Danny Grove
   * @version 1.0
   * @param $array
   * @return $string of placeholders
   */
  public  function buildNamedPlaceholders($array) {
    $placeholders = "";

    foreach($array as $key => $value) {
      $placeholders .= ":$key, ";
    }

    $placeholders = substr($placeholders, 0, -2);

    return $placeholders;
  }

/** 
   * Takes a key => value array and builds named placeholders for update statements
   * @author Danny Grove
   * @version 1.0
   * @param $fields
   * @return string of set placeholders for use in query
   */
  public function condenseKeysWithNamedPlaceholders($fields) {
    $keysWithPlaceholders = array();
    foreach($fields as $key => $value) {
      array_push($keysWithPlaceholders, "$key=:$key");
    }
    $string = implode(", ", $keysWithPlaceholders);
    return $string;
  }

  /**
   * Takes in an array of fields and returns an imploded string with named placeholders added
   * added
   * @author Danny Grove
   * @version 1.0
   * @param $fields
   * @return $string of fields with placeholders for a query
   */
  public  function condenseWithNamedPlaceholders($fields) {
    foreach($fields as $key => &$value) {
      $value .= "=:$key";
    }
    $fields = implode(", ", $fields);

    return $fields;
  }


  /**
   * Takes in an array and prints the resulting JSON
   * encoded object
   * @author Danny Grove
   * @version 1.0
   * @param $array
   */
  public  function print2json($array) {
    print(json_encode($array));
  }
}

function buildStatement($tableStatement, $dbType) {
  if (strtoupper($dbType) == "MYSQL") {
    $newStmt = $tableStatement['insert']. " ON DUPLICATE KEY UPDATE"; 
  } else if (strtoupper($dbType == "MSSQL")) {
    $newStmt = "IF NOT EXISTS (".$tableStatement['select'].") BEGIN ".$tableStatement['insert']." END ELSE BEGIN ".$tableStatement['update']." END";
  }
  return $newStmt;
}

function makeNamedKey($array) {
  $nameKey = array();
  foreach($array as $key => $value) {
    $nameKey[":$key"] = $value;
  }
  return $nameKey;
}

function makeSelectors($array) {
  $selectors = array();
  foreach($array as $key => $value) {
    array_push($selectors, $value);
  }
  $selectors = implode(',', $selectors);
  return $selectors;
}

?>

