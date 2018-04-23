<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);
$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
    "allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$cors = new \CorsSlim\CorsSlim($corsOptions);
 
$app->add($cors);
// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();

function getEmployes() {
    $sql = "select * FROM employee";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $emp = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
       return json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getEmployee($request) {
            $id = 0;;
            $id =  $request->getAttribute('id');
            if(empty($id)) {
                        echo '{"error":{"text":"Id is empty"}}';
            }
    try {
                        $db = getConnection();
        $sth = $db->prepare("SELECT * FROM employee WHERE id=$id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $todos = $sth->fetchObject();
                        return json_encode($todos);
    } catch(PDOException $e) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
function addEmployee($request) {
    $emp = json_decode($request->getBody());
           
    $sql = "INSERT INTO employee (employee_name, employee_salary, employee_age) VALUES (:name, :salary, :age)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $emp->name);
        $stmt->bindParam("salary", $emp->salary);
        $stmt->bindParam("age", $emp->age);
        $stmt->execute();
        $emp->id = $db->lastInsertId();
        $db = null;
        echo json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
 
function updateEmployee($request) {
    $emp = json_decode($request->getBody());
            $id = $request->getAttribute('id');
    $sql = "UPDATE employee SET employee_name=:name, employee_salary=:salary, employee_age=:age WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $emp->name);
        $stmt->bindParam("salary", $emp->salary);
        $stmt->bindParam("age", $emp->age);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo json_encode($emp);
    } catch(PDOException $e) {
       echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
 
function deleteEmployee($request) {
            $id = $request->getAttribute('id');
    $sql = "DELETE FROM employee WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        //$stmt->execute();
        $db = null;
                        echo '{"error":{"text":"successfully! deleted Records"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
 
function getConnection() {
    $dbhost="localhost";
    $dbuser="root";
    $dbpass="";
    $dbname="lms";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

function getLeaveType() {
    $sql = "select * FROM leave_type";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $emp = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
       return json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getLeaveRequest($request) {
            $id = 0;;
            $id =  $request->getAttribute('id');
            if(empty($id)) {
                        echo '{"error":{"text":"Id is empty"}}';
            }
    try {
        $db = getConnection();
        $sth = $db->prepare("SELECT * FROM leave_requests WHERE user_id=$id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $todos = $sth->fetchObject();
                        return json_encode($todos);
    } catch(PDOException $e) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
function getEmployeeLeaveStatus($request) {
            $id = 0;;
            $id =  $request->getAttribute('id');
            if(empty($id)) {
                        echo '{"error":{"text":"Id is empty"}}';
            }
    try {
        $db = getConnection();
        $sth = $db->prepare("SELECT * FROM employees_leave_status WHERE user_id=$id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $todos = $sth->fetchObject();
                        return json_encode($todos);
    } catch(PDOException $e) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getLeaveSummary($request) {
            $id = 0;
            $id =  $request->getAttribute('id');
            if(empty($id)) {
                        echo '{"error":{"text":"Id is empty"}}';
            }
    try {
        $db = getConnection();
        $sth = $db->prepare("SELECT * FROM leave_requests 
		LEFT JOIN leave_type ON leave_requests.leave_type_id = leave_type.id 
		LEFT JOIN employees_leave_status ON  leave_requests.leave_type_id = employees_leave_status.leave_type_id 
		WHERE leave_requests.user_id = $id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $todos = $sth->fetchAll(PDO::FETCH_OBJ);
                        return json_encode($todos);
    } catch(PDOException $e) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function addLeave($request) {
    $emp = json_decode($request->getBody());
           
    $sql = "INSERT INTO leave_requests (user_id, leave_type_id, leave_start, leave_end, return_date, requested_days, reason, leave_request_status) VALUES (:user_id, :leave_type_id, :leave_start, :leave_end, :return_date, :requested_days, :reason, :leave_request_status)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $emp->user_id);
        $stmt->bindParam("leave_type_id", $emp->leave_type_id);
        $stmt->bindParam("leave_start", $emp->leave_start);
        $stmt->bindParam("leave_end", $emp->leave_end);
        $stmt->bindParam("return_date", $emp->return_date);
        $stmt->bindParam("requested_days", $emp->requested_days);
        $stmt->bindParam("reason", $emp->reason);
        $stmt->bindParam("leave_request_status", $emp->leave_request_status);
        $stmt->execute();
        $emp->id = $db->lastInsertId();
        $db = null;
        echo json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}