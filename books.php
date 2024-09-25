<!DOCTYPE html>
<html lang='en-GB'>
<head>
<title>Booking Form</title>
<link rel="stylesheet" href="books.css">
</head>
<body>
<?php
session_start();

$db_hostname = "studdb.csc.liv.ac.uk";
$db_database = "sgschuri";
$db_username = "sgschuri";
$db_password = "Manoj";
$db_charset = "utf8mb4";

$dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$db_charset";
$opt = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
);
try {
    $pdo = new PDO($dsn,$db_username,$db_password,$opt);

    // Get topics for topic dropdown option
    $topicRowQuery = "SELECT distinct(BookDescription) from Books where NumberOfCopies > 0 ORDER BY BookDescription";
    $topicRowStmt = $pdo->prepare($topicRowQuery);
    $topicRowStmt->execute();
    
    // Initialize an empty array to store results
    $topicRowArray = array();

    // Fetch all rows of topic
    while ($row = $topicRowStmt->fetch(PDO::FETCH_ASSOC)){
        $topicRowArray[] = $row['BookDescription'];
    }
    
    // Get Capacity for checking available sessions
    $capRowQuery = "SELECT NumberOfCopies from Books";
    $capRowStmt = $pdo->prepare($capRowQuery);
    $capRowStmt->execute();
    
    // Initialize an empty array to store results
    $capRowArray = array();

    // Fetch all rows of topic
    while ($row = $capRowStmt->fetch(PDO::FETCH_ASSOC)){
        $capRowArray[] = $row['NumberOfCopies'];
    }
    $noSessions = true;
      foreach ($capRowArray as $value) {
        if ($value != 0) {
          $noSessions = false;
          break;
        }
      }
    if($noSessions){
    echo "<a>No Sessions Available</a>";
    return;
    }
    echo "<script>console.log('Selected after: " . json_encode($capRowArray) . "');</script>"; 
    
    // Initialize name variable
    $name = ""; 
    // Initialize email variable
    $email = "";
    

    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["Topicdropdown"])) {
          $_SESSION['selectedTopic'] = $_POST["Topicdropdown"];
          // Store topic selected in a local variable
          $selectedTopic = $_SESSION['selectedTopic'];
        }
        if (isset($_POST["Timesdropdown"])){
          $_SESSION['selectedTime'] = $_POST["Timesdropdown"];
          // Store time selected in a local variable
          $selectedTime = $_SESSION['selectedTime'];
        }
        if (isset($_POST["Enteredname"])) {
          // Store name in a local variable
          $name = $_SESSION['Enteredname']; 
        }
        if (isset($_POST["Enteredemail"])) {
          // store email in a local variable
          $email = $_SESSION['Enteredemail']; 
        }
        
    
    }
    
    echo"<table class='table1'>";

    echo"<tr><td colspan='2'><h1>IT Training Booking Form</h1></td></tr>";
  
    // Topic Dropdown Section
    echo "<tr><form method='post' id='topic' name='topic'>
    <td>Books:</td>
    <td> <select name='Topicdropdown' onChange='document.topic.submit()'>
    &nbsp;&nbsp;<option value=''";
    if (empty($_SESSION['selectedTopic'])) {
        echo " selected";
    }
    echo ">Select Book</option>";
    foreach ($topicRowArray as $value) {
      echo "<option value='$value'";
      if ($selectedTopic && $selectedTopic == $value) {
          echo "selected";
      }
      echo ">$value</option>";
    }
    echo "</select><td>
    </form></tr><br>";
    

    // Times Dropdown Section
    
    //Get time for time dropdown option
    $timesRowQuery = "SELECT Researcher FROM Books WHERE BookDescription = :selectedTopic AND NumberOfCopies > 0 ";
    $timesRowStmt = $pdo->prepare($timesRowQuery);
    $timesRowStmt->bindParam(':selectedTopic', $selectedTopic);
    $timesRowStmt->execute();
    $timesRowArray = $timesRowStmt->fetchAll(PDO::FETCH_COLUMN);

    echo"<tr><form method='post' id='times' name='times'>
    <td>Researcher:</td>
    <td><select name='Timesdropdown' onChange='document.times.submit()'>
    <option value='None'>Select Researcher</option>";
    foreach ($timesRowArray as $value) {
      echo "<option value='$value'";
      if ($selectedTime && $selectedTime == $value) {
          echo "selected";
      }
      echo ">$value</option>";
    }
    echo "</select></td>";


    // Added a hidden input field to hold the selected topic value
    echo "<input type='hidden' name='Topicdropdown' value='$selectedTopic'>";
    echo "</form><tr>";

    // Name and Email Form
    echo "<tr><form name='nameAndEmail' method='post' id='nameAndEmail'>
    <tr><td>Name:</td><td><input type='text' name='Enteredname'  size='100' value='" . (isset($_SESSION['enteredName']) ? $_SESSION['enteredName'] : $name) . "'></td></tr><br>
    <tr><td>Email :</td><td> <input type='text' name='Enteredemail' size='100' value='" . (isset($_SESSION['enteredEmail']) ? $_SESSION['enteredEmail'] : $email) . "'></td></tr><br><br>
    <tr><td colspan = '2'><input type='submit' value='Submit' name='submit'style='width: 570px; background-color: #909fd3;'></td></tr>
    </form></tr>";
    echo"<figcaption style='
    POSITION: STATIC;
'>&copy;University of Liverpool</figcaption>";
    echo"</table>";
    
    
     
    // On submit checking required input
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit']))  {
      
      $success = false;
      
      $book = true;
        
      echo"<table><tr>";  
      $topic = $_SESSION['selectedTopic'];
      //echo "<script>console.log('Selected Topic: " . json_encode($topic) . "');</script>";
      // Checking input topic
      if(is_null($topic) || $topic == "" ){
      
        //$success = false;
        $book = false;
        //$_SESSION['selectedTime'] = "";
        echo"<td class='alert'> Please select the Book </td>";
        //return;
      }  
        
      $researcher = true;
      $time = $_SESSION['selectedTime'];
      // echo "<script>console.log('Selected Time: " . json_encode($time) . "');</script>";
      // Checking input time
      if(is_null($time) || $time == ""){
        //$success = false;
        $researcher = false;
        //$_SESSION['selectedTopic']="";
        echo"<td class='alert'> Please select the Researcher </td>";
        //return;
      }
      

      // Retrieve form data
      // Retrieve the entered name from POST data
      $name = $_POST['Enteredname'];
      
      // Define the regular expression pattern for the name constraints
      $namePattern = '/^[A-Za-z\'(][A-Za-z\'\-]*(?:[A-Za-z\'\-]+[A-Za-z\'(])?$/';
      
      $nameCheck = false;
      // Check if the name satisfies the condition
      if (empty($name) || !preg_match($namePattern, $name) || preg_match('/--|\'\'|\'\'\'\'/', $name)) {
          //$success = false;
          $nameCheck = false;
          // Display error message
          echo "<td class='alert'> Please enter a valid name. </td>";
      } else {
          // Proceed with further processing
          //$success = true;
          $nameCheck = true;
          // Additional code to handle the valid name
      }


        
      $email = $_POST['Enteredemail'];

      $emailPattern = '/^(?!.*[.-]{2})[a-zA-Z0-9._%+-]+(?<![.-])@[a-zA-Z0-9.-]+(?<![.-])\.[a-zA-Z]{2,}$/';
      
      $emailCheck = false;
      // Check if the email satisfies the condition
      if (!preg_match($emailPattern, $email)) {
          //$success = false;
          $emailCheck = false;
          //$_SESSION['selectedTopic']="";
          //$_SESSION['selectedTime'] = "";
          echo "<td class='alert'> Invalid email address. </td>";
          //return;
      } else {
          $emailCheck = true;
          //$success = true;
          // Proceed with further processing
      }
      
      // // Check capacity for session 
      // $capacityRowQuery = "SELECT NumberOfCopies from Books where BookDescription = :selectedTopic and Researcher = :selectedTime";
      // $capacityRowStmt = $pdo->prepare($capacityRowQuery);
      // $capacityRowStmt->bindParam(':selectedTopic', $topic);
      // $capacityRowStmt->bindParam(':selectedTime', $time);
      // $capacityRowStmt->execute();
      // $capacityRowArray = $capacityRowStmt->fetchAll(PDO::FETCH_COLUMN);
      
      // //echo "<script>console.log('capacityValueter: " .json_encode($capacityRowArray) . "');</script>";
      
      // $zeroCapacity = false; 
      // foreach ($capacityRowArray as $value) {
      //   if ($value == 0) {
      //     $zeroCapacity = true;
      //     break;
      //   }
      // }
      // if ($zeroCapacity) {
      //   $success = false;
      //   echo "<td class='unsuccessful'> Booking full </td>";
      // }

      $sql = "SELECT * FROM Orders WHERE BookDescription = :selectedTopic AND Researchers = :selectedTime AND ReceiverName = :Enteredname AND Email = :Enteredemail";
      $sqlStmt = $pdo->prepare($sql);
      $sqlStmt->bindParam(':selectedTopic', $topic);
      $sqlStmt->bindParam(':selectedTime', $time);
      $sqlStmt->bindParam(':Enteredname', $name);
      $sqlStmt->bindParam(':Enteredemail', $email);
      $sqlStmt->execute();
      
      // Fetch the results
      $results = $sqlStmt->fetchAll(PDO::FETCH_ASSOC);

      // Get the number of rows
      // $numRows = $sqlStmt->rowCount();
      
      if($nameCheck and $emailCheck and $book and $researcher){
        $success = true;
      }else{
        $success = false;
      }
      
      
      
      if($success){
        $zeroRowCount = true;
      // Check if any rows are returned
        if ($sqlStmt->rowCount() == 0) {
          $zeroRowCount = true;
        } else {
          $zeroRowCount = false;
          echo "<a class='unsuccessful'> Book is already taken </a>";
            // echo "Query returned true (rows found)";
        }

        if($zeroRowCount){
          $success = true;
        }else{
          $success = false;
        }
      }
      


      
      echo"</tr></table>";
        
      if(!$success){
      
        //echo "<script>console.log('not successs: " . json_encode($topic) . "');</script>";
        $_SESSION['Enteredname'] = $name;
        $_SESSION['Enteredemail'] = $email;
        $_SESSION['selectedTopic'] = $selectedTopic;
        $_SESSION['selectedTime'] = $selectedTime;
        echo "<a class='unsuccessful'> Booking Unsuccessful </a>";
        //return;
        
      }else{
        
        echo "<a class='sucucessfull'> Booking Successful </a>";
        //echo "<script>console.log('Selected after: " . json_encode($topic) . "');</script>";
        //echo "<script>console.log('Selected after: " . json_encode($time) . "');</script>";
        // echo "<script>console.log('Selected after: " . json_encode($name) . "');</script>";
        // echo "<script>console.log('Selected after: " . json_encode($email) . "');</script>";
   
        // On success insert data into database
        $stmtInsert = $pdo->prepare(
          "insert into Orders (BookDescription,Researchers,ReceiverName,Email) values(:topic,:time,:name,:email)");
        $stmtInsert = $stmtInsert->execute(
          array("topic" => $_SESSION['selectedTopic'],"time" => $_SESSION['selectedTime'],"name" => $name,"email" => $email));
           
        // On success update capacity into database  
        //$stmtUpdate = $pdo->prepare(
          //"update training SET capacity = capacity - 1 WHERE topic = :topic;");
        $updateQuery = "UPDATE Books SET NumberOfCopies = NumberOfCopies   - 1 WHERE BookDescription = :topic and Researcher = :time";

        $statement = $pdo->prepare($updateQuery);
        
        // Bind the values to parameters
        $statement->bindParam(':topic', $topic);
        $statement->bindParam(':time', $time);
        $statement->execute();
        
        $_SESSION['selectedTopic'] = "";
        $_SESSION['selectedTime'] = "";
      
          
      }
          
      // get data from database
      $reservationQuery = $pdo->query("SELECT * FROM Orders");
      $reservationData = $reservationQuery->fetchAll(PDO::FETCH_ASSOC);
  
      // Output booking table
      echo "<table class='table2' style='width: auto;'>";
      echo "<tr class='table2'>";
      foreach ($reservationData[0] as $key => $value) {
        echo "<th class='table2'>$key</th>";
      }
      echo "</tr>";
      foreach ($reservationData as $row) {
        echo "<tr class='table2'>";
        foreach ($row as $cell) {
          echo "<td class='table2'>$cell</td>";
        }
        echo "</tr>";
      }
      echo "</table>";    
    }
    

    $pdo = NULL;
} catch (PDOException $e) {
    exit("PDO Error: ".$e->getMessage()."<br>"); 
}
//References
//1.Practical 15 and 16
//2.W3school.com
//3.https://www.php.net/

?>
</body>

</html>

