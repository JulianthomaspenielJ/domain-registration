<?php
error_reporting(1);
error_reporting(E_ALL);

require_once "include/apiResponseGenerator.php";
require_once "include/dbConnection.php";
class CLIENTCONTACTPERSONMODEL extends APIRESPONSE
{
    private function processMethod($data, $loginData)
    {

        switch (REQUESTMETHOD) {
            case 'GET':
                $urlPath = $_GET['url'];
                $urlParam = explode('/', $urlPath);
                if ($urlParam[1] == "get") {
                    $result = $this->getClientContactPerson($data, $loginData);
                } else {
                    throw new Exception("Method not allowed!");
                }
                return $result;
                break;
            case 'POST':
                $urlPath = $_GET['url'];
                $urlParam = explode('/', $urlPath);
                if ($urlParam[1] === 'create') {
                    $result = $this->createClientContactPerson($data, $loginData);
                    return $result;
                } elseif ($urlParam[1] === 'list') {
                    $result = $this->getClientContactPersonDetails($data, $loginData);
                    return $result;
                } else {
                    throw new Exception("Method not allowed!");
                }
                break;
            case 'PUT':
                $urlPath = $_GET['url'];
                $urlParam = explode('/', $urlPath);
                if ($urlParam[1] == "update") {
                    $result = $this->updateClientContactPerson($data, $loginData);
                } else {
                    throw new Exception("Method not allowed!");
                }
                return $result;
                break;
            case 'DELETE':
                $urlPath = $_GET['url'];
                $urlParam = explode('/', $urlPath);
                if ($urlParam[1] == "delete") {
                    $result = $this->deleteClientContactPerson($data, $loginData);
                } else {
                    throw new Exception("Method not allowed!");
                }
                return $result;
                break;
            default:
                $result = $this->handle_error();
                return $result;
                break;
        }
    }
    // Initiate db connection
    private function dbConnect()
    {
        $conn = new DBCONNECTION();
        $db = $conn->connect();
        return $db;
    }

    /**
     * Function is to get the for particular record
     *
     * @param array $data
     * @return multitype:
     */
    public function getClientContactPersonDetails($data, $loginData)
    {
        try {
            $responseArray = "";
            $res = array();
            $db = $this->dbConnect();
            $totalRecordCount = $this->getTotalCount($loginData);
            if (empty($data['page_index']) && $data['page_index'] != 0) {
                throw new Exception("page_index should not be empty");
            }
            if (empty($data['data_length'])) {
                throw new Exception("data_length should not be empty");
            }
            $start_index = $data['page_index'] * $data['data_length'];
            $end_index = $data['data_length'];
            $sql = "SELECT id,client_name,username,email_id, mobile_no,address,created_by, created_date, updated_date FROM tbl_clients   WHERE status = 1 and created_by = " . $loginData['user_id'] . " ORDER BY id DESC LIMIT " . $start_index . "," . $end_index . "";

            $result = $db->query($sql);
            $row_cnt = mysqli_num_rows($result);
            $clientData = array();
            $contactData = array();
            if ($row_cnt > 0) {
                while ($product_row = $result->fetch_assoc()) {

                    $sql2 = "SELECT id,name,email_id,username,mobile_no,client_id ,designation_id, created_date, updated_date FROM tbl_contact_person   WHERE status = 1 and created_by = " . $loginData['user_id'] . " and client_id=" . $product_row['id'] . " ORDER BY id DESC LIMIT " . $start_index . "," . $end_index . "";
                    $result2 = $db->query($sql2);
                    while ($product_row2 = $result2->fetch_assoc()) {

                        $sql1 = "SELECT id,designation FROM tbl_designation  WHERE status = 1  and id = " . $product_row2['designation_id'];
                        $result1 = $db->query($sql1);
                        while ($product_row1 = $result1->fetch_assoc()) {

                            $contact = array(
                                'id' => $product_row2['id'],
                                'name' => $product_row2['name'],
                                'username' => $product_row['username'],
                                'email_id' => $product_row2['email_id'],
                                'mobile_no' => $product_row2['mobile_no'],
                                "designation" => $product_row1['designation'],
                                'created_date' => $product_row2['created_date'],
                                'updated_date' => $product_row2['updated_date']


                            );
                            array_push($contactData, $contact);
                        }
                    }
                    $client = array(
                        'id' => $product_row['id'],
                        'client_name' => $product_row['client_name'],
                        'username' => $product_row['username'],
                        'email_id' => $product_row['email_id'],
                        'mobile_no' => $product_row['mobile_no'],
                        'address' => $product_row['address'],
                        "contactData" => $contactData,

                    );
                    array_push($clientData, $client);
                }
                $responseArray = array(
                    "pageIndex" => $start_index,
                    "dataLength" => $end_index,
                    "totalRecordCount" => $totalRecordCount,
                    'clientData' => $clientData,
                );

            }
            if ($responseArray) {
                $resultArray = array(

                    "code" => "200",
                    "message" => "Client details fetched successfully",

                    "result" => $responseArray,
                );
                return $resultArray;
            } else {
                return array(

                    "code" => "404",
                    "message" => "No data found..."

                );
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Function is to get the for particular record
     *
     * @param array $data
     * @return multitype:
     */
    public function getClientContactPerson($data, $loginData)
    {
        try {
            $id = $data[2];
            $db = $this->dbConnect();
            if (empty($data[2])) {
                throw new Exception("Bad request");
            }

            $responseArray = "";
            $clientData = array();
            $db = $this->dbConnect();
            $sql = "SELECT id,client_name,username,email_id, mobile_no,address,created_by, created_date, updated_date FROM tbl_clients	WHERE status = 1 and created_by = " . $loginData['user_id'] . " and id =$id";
            $result = $db->query($sql);
            $row_cnt = mysqli_num_rows($result);
            $clientData = array();
            $contactData = array();
            if ($row_cnt > 0) {
                $product_row = $result->fetch_assoc();
                // echo json_encode($product_row);

                $sql2 = "SELECT id,name,username,email_id,mobile_no,client_id ,designation_id, created_date, updated_date FROM tbl_contact_person   WHERE status = 1 and created_by = " . $loginData['user_id'] . " and client_id=" . $product_row['id'];
                $result2 = $db->query($sql2);
                while ($product_row2 = $result2->fetch_assoc()) {

                    $sql1 = "SELECT id,designation FROM tbl_designation  WHERE status = 1  and id = " . $product_row2['designation_id'];
                    $result1 = $db->query($sql1);
                    while ($product_row1 = $result1->fetch_assoc()) {

                        $contact = array(
                            'id' => $product_row2['id'],
                            'name' => $product_row2['name'],
                            'username' => $product_row['username'],
                            'email_id' => $product_row2['email_id'],
                            'mobile_no' => $product_row2['mobile_no'],
                            "designation" => $product_row1['designation'],
                            'created_date' => $product_row2['created_date'],
                            'updated_date' => $product_row2['updated_date']


                        );
                        array_push($contactData, $contact);
                    }
                }
                $client = array(
                    'id' => $product_row['id'],
                    'client_name' => $product_row['client_name'],
                    'username' => $product_row['username'],
                    'email_id' => $product_row['email_id'],
                    'mobile_no' => $product_row['mobile_no'],
                    'address' => $product_row['address'],
                    "contactData" => $contactData,

                );
                array_push($clientData, $client);
                $responseArray = array(
                    'clientData' => $clientData,
                );
                // while ($data = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                //     array_push($res, $data);
                // }
                // $responseArray = array(
                //     "pageIndex" => $start_index,
                //     "dataLength" => $end_index,
                //     "totalRecordCount" => $totalRecordCount,
                //     'clientData' => $res,
                // );
            }
            if ($responseArray) {
                $resultArray = array(

                    "code" => "200",
                    "message" => "Client details fetched successfully",

                    "result" => $responseArray,
                );
                return $resultArray;
            } else {
                return array(

                    "code" => "404",
                    "message" => "No data found..."

                );
            }
        } catch (Exception $e) {
            echo "ssdfd";
            throw new Exception($e->getMessage());
        }
    }
    /**
     * Post/Add sale
     *
     * @param array $data
     * @return multitype:string
     */
    private function createClientContactPerson($data, $loginData)
    {


        try {
            $db = $this->dbConnect();
            $validationData = array("username" => $data['username'], "client_name" => $data['client_name'], "email_id" => $data['email_id']);
            $this->validateInputDetails($validationData);
            $sql = "SELECT id FROM tbl_clients	WHERE status = 1  and username ='" . $data['username'] . "'";
            $result = $db->query($sql);
            $row_cnt = mysqli_num_rows($result);

            if ($row_cnt != 0) {
                throw new Exception("Client username " . $data['username'] . " already found");
            }

            for ($x = 0; $x < count($data['contactData']); $x++) {
                $validationData = array("name" => $data['contactData'][$x]['name'], "username" => $data['contactData'][$x]['username'], "email_id" => $data['contactData'][$x]['email_id'], "designation_id" => $data['contactData'][$x]['designation_id']);
                $this->validateInputDetails($validationData);

            }
            // $validationData = array("client_name" => $data['client_name'], "email_id" => $data['email_id']);
            // // 
            $dateNow = date("Y-m-d H:i:s");

            $insertQuery = "INSERT INTO tbl_clients (client_name,username,email_id, mobile_no,address,status, created_by, created_date) VALUES ('" . $data['client_name'] . "','" . $data['username'] . "','" . $data['email_id'] . "','" . $data['mobile_no'] . "','" . $data['address'] . "','" . '1' . "','" . $loginData['user_id'] . "','$dateNow')";
            if ($db->query($insertQuery) === true) {
                $last_id = $db->insert_id;

                for ($x = 0; $x < count($data['contactData']); $x++) {

                    $sql = "SELECT id FROM tbl_contact_person	WHERE status = 1 and username ='" . $data['contactData'][$x]['username'] . "'";
                    $result = $db->query($sql);
                    $row_cnt = mysqli_num_rows($result);

                    if ($row_cnt != 0) {
                        throw new Exception("ContactPerson username " . $data['contactData'][$x]['username'] . " already found");
                    }
                    $insertQuery1 = "INSERT INTO tbl_contact_person (name,username, email_id,mobile_no,client_id ,designation_id ,status, created_by, created_date) VALUES ('" . $data['contactData'][$x]['name'] . "','" . $data['contactData'][$x]['username'] . "','" . $data['contactData'][$x]['email_id'] . "','" . $data['contactData'][$x]['mobile_no'] . "','" . $last_id . "','" . $data['contactData'][$x]['designation_id'] . "','" . '1' . "','" . $loginData['user_id'] . "','$dateNow')";
                    if ($db->query($insertQuery1)) {
                        $statusCode = "200";
                        $statusMessage = "Client details created successfully";

                    } else {
                        $statusCode = "500";
                        $statusMessage = "Unable to create Client details, please try again later";
                    }
                }

                $db->close();

            } else {
                $statusCode = "500";
                $statusMessage = "Unable to create Client details, please try again later";
            }
            $resultArray = array(

                "code" => $statusCode,
                "message" => $statusMessage

            );
            return $resultArray;

        } catch (Exception $e) {
            return array(
                "code" => "401",
                "message" => $e->getMessage()

            );
        }
    }


    /**
     * Put/Update a Sale
     *
     * @param array $data
     * @return multitype:string
     */
    private function updateClientContactPerson($data, $loginData)
    {

        try {
            $db = $this->dbConnect();
            $validationData = array("id" => $data['id'], "client_name" => $data['client_name'], "email_id" => $data['email_id']);
            $this->validateInputDetails($validationData);
            for ($x = 0; $x < count($data['contactData']); $x++) {
                $validationData = array("id" => $data['id'], "name" => $data['contactData'][$x]['name'], "email_id" => $data['contactData'][$x]['email_id'], "designation_id" => $data['contactData'][$x]['designation_id']);
                $this->validateInputDetails($validationData);
            }
            $dateNow = date("Y-m-d H:i:s");
            $updateQuery = "UPDATE tbl_clients SET client_name = '" . $data['client_name'] . "', email_id = '" . $data['email_id'] . "',mobile_no = '" . $data['mobile_no'] . "', address = '" . $data['address'] . "',updated_by = '" . $loginData['user_id'] . "',updated_date = '$dateNow' WHERE id = " . $data['id'] . "";
            if ($db->query($updateQuery) === true) {

                for ($x = 0; $x < count($data['contactData']); $x++) {

                    $sql2 = "SELECT id FROM tbl_contact_person	WHERE status = 1 and created_by = " . $loginData['user_id'] . " and id =" . $data['contactData'][$x]['id'] . " and client_id=" . $data['id'];
                    $result2 = $db->query($sql2);
                    $row_cnt = mysqli_num_rows($result2);
                    if ($row_cnt == 0) {
                        throw new Exception("ContactPerson id " . $data['contactData'][$x]['id'] . " not found");
                    }
                    $updateQuery1 = "UPDATE tbl_contact_person SET name = '" . $data['contactData'][$x]['name'] . "', email_id = '" . $data['contactData'][$x]['email_id'] . "',mobile_no = '" . $data['contactData'][$x]['mobile_no'] . "',updated_by = '" . $loginData['user_id'] . "',updated_date = '$dateNow' WHERE id = " . $data['contactData'][$x]['id'] . " and client_id=" . $data['id'] . "";
                    if ($db->query($updateQuery1)) {
                        $statusCode = "200";
                        $statusMessage = "Client details updated successfully";

                    } else {
                        $statusCode = "500";
                        $statusMessage = "Unable to create Client details, please try again later";
                    }
                }
                $db->close();

            } else {
                $statusCode = "500";
                $statusMessage = "Unable to update client details, please try again later";
            }
            $resultArray = array(

                "code" => $statusCode,
                "message" => $statusMessage

            );
            return $resultArray;
        } catch (Exception $e) {
            return array(

                "code" => "401",
                "message" => $e->getMessage()

            );
        }
    }

    private function deleteClientContactPerson($data, $loginData)
    {
        try {
            $id = $data[2];
            $db = $this->dbConnect();
            if (empty($data[2])) {
                throw new Exception("Bad request id is required");
            }
            $sql = "SELECT id FROM tbl_clients	WHERE status = 1 and created_by = " . $loginData['user_id'] . " and id =$id";
            $result = $db->query($sql);
            $row_cnt = mysqli_num_rows($result);
            if ($row_cnt == 0) {
                throw new Exception("id " . $id . " not found");
            }
            $deleteQuery1 = "UPDATE tbl_contact_person set status=0 WHERE client_id = " . $id . "";
            if ($db->query($deleteQuery1) === true) {
                $deleteQuery = "UPDATE tbl_clients set status=0 WHERE id = " . $id . "";
                if ($db->query($deleteQuery) === true) {
                    $db->close();
                    $statusCode = "200";
                    $statusMessage = "Client details deleted successfully";

                } else {
                    $statusCode = "500";
                    $statusMessage = "Unable to delete client details, please try again later";
                }
            } else {
                $statusCode = "500";
                $statusMessage = "Unable to delete client details, please try again later";
            }

            $resultArray = array(

                "code" => $statusCode,
                "message" => $statusMessage

            );
            return $resultArray;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());

        }
    }
    public function validateInputDetails($validationData)
    {
        foreach ($validationData as $key => $value) {
            if (empty($value) || trim($value) == "") {
                throw new Exception($key . " should not be empty!");
            }
        }
    }
    /**
     * Validate function for sale create
     *
     * @param array $data
     * @throws Exception
     * @return multitype:string NULL
     */

    // public function validateInputDateFormate($validationDateData)
    // {

    //     foreach ($validationDateData as $key => $value) {
    //         if ($this->validateDate($value)) {

    //         } else {
    //             throw new Exception($key . " invalid date format!");
    //         }
    //     }
    // }

    // function validateDate($date, $format = 'Y-m-d')
    // {
    //     $dateTimeObj = DateTime::createFromFormat($format, $date);
    //     return $dateTimeObj && $dateTimeObj->format($format) === $date;
    // }



    private function getTotalCount($loginData)
    {
        try {
            $db = $this->dbConnect();
            $sql = "SELECT * FROM tbl_clients WHERE status = 1 and created_by = " . $loginData['user_id'] . "";
            $result = $db->query($sql);
            $row_cnt = mysqli_num_rows($result);
            return $row_cnt;
        } catch (Exception $e) {
            return array(
                "result" => "401",
                "message" => $e->getMessage(),
            );
        }
    }
    private function getTotalPages($dataCount)
    {
        try {
            $pages = null;
            if (MAX_LIMIT) {
                $pages = ceil((int) $dataCount / (int) MAX_LIMIT);
            } else {
                $pages = count($dataCount);
            }
            return $pages;
        } catch (Exception $e) {
            return array(
                "result" => "401",
                "message" => $e->getMessage(),
            );
        }
    }
    // Unautherized api request
    private function handle_error()
    {
    }
    /**
     * Function is to process the crud request
     *
     * @param array $request
     * @return array
     */
    public function processList($request, $token)
    {
        try {
            $responseData = $this->processMethod($request, $token);
            $result = $this->response($responseData);
            return $responseData;
        } catch (Exception $e) {
            return array(

                "code" => "401",
                "message" => $e->getMessage()

            );
        }
    }
}
