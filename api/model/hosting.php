<?php
// error_reporting(E_ALL);

require_once "include/apiResponseGenerator.php";
require_once "include/dbConnection.php";
class HOSTINGMODEL extends APIRESPONSE
{
    private function processMethod($data, $loginData)
    {

        switch (REQUESTMETHOD) {
            case 'GET':
                $urlPath = $_GET['url'];
                $urlParam = explode('/', $urlPath);
                if ($urlParam[1] == "get") {
                    $result = $this->getHosting($data, $loginData);
                } else {
                    throw new Exception("Unable to proceed your request!");
                }
                return $result;
                break;
            case 'POST':
                $urlPath = $_GET['url'];
                $urlParam = explode('/', $urlPath);
                if ($urlParam[1] === 'create') {
                    $result = $this->createHosting($data, $loginData);
                    return $result;
                } elseif ($urlParam[1] === 'list') {
                    $result = $this->getHostingDetails($data, $loginData);
                    return $result;
                } else {
                    throw new Exception("Unable to proceed your request!");
                }
                break;
            case 'PUT':
                $urlPath = $_GET['url'];
                $urlParam = explode('/', $urlPath);
                if ($urlParam[1] === "update") {
                    $result = $this->updateHosting($data, $loginData);
                } else {
                    throw new Exception("Unable to proceed your request!");
                }
                return $result;
                break;
            case 'DELETE':
                $urlPath = $_GET['url'];
                $urlParam = explode('/', $urlPath);
                if ($urlParam[1] === "delete") {
                    $result = $this->deleteHosting($data);
                } else {
                    throw new Exception("Unable to proceed your request!");
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
    public function getHostingDetails($data, $loginData)
    {
        try {
            $responseArray = "";
            $res = array();
            $db = $this->dbConnect();
            $totalRecordCount = $this->getTotalCount($loginData);
            // $validationData = array("page_index" => $data['page_index'], "data_length" => $data['data_length']);
            // $this->validateInputDetails($validationData);
            if (empty ($data['page_index']) && $data['page_index'] != 0) {
                throw new Exception("page_index should not be empty");
            }
            if (empty ($data['data_length'])) {
                throw new Exception("data_length should not be empty");
            }
            $start_index = $data['page_index'] * $data['data_length'];
            $end_index = $data['data_length'];

            $sql = "SELECT id,hosting_server_type,registered_date, expired_date, email_id, mobile_no, created_by, created_date, updated_date FROM tbl_Hosting   WHERE status = 1 and created_by = " . $loginData['user_id'] . " ORDER BY id DESC LIMIT " . $start_index . "," . $end_index . "";

            $result = $db->query($sql);
            $row_cnt = mysqli_num_rows($result);
            if ($row_cnt > 0) {
                while ($data = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    array_push($res, $data);
                }
                $responseArray = array(
                    "pageIndex" => $start_index,
                    "dataLength" => $end_index,
                    "totalRecordCount" => $totalRecordCount,
                    'HostingData' => $res,
                );
            }
            if ($responseArray) {
                $resultArray = array(

                    "code" => "200",
                    "message" => "Hosting details fetched successfully",

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
    public function getHosting($data, $loginData)
    {
        try {
            $id = $data[2];
            $db = $this->dbConnect();
            $validationData = array("Id" => $data[2]);
            $this->validateInputDetails($validationData);
            if (empty ($data[2])) {
                throw new Exception("Bad request");
            }

            $responseArray = "";
            $db = $this->dbConnect();
            $sql = "SELECT id,hosting_server_type,registered_date, expired_date, email_id, mobile_no, created_by, created_date, updated_date FROM tbl_hosting	WHERE status = 1 and created_by = " . $loginData['user_id'] . " and id =$id";
            $result = $db->query($sql);
            $row_cnt = mysqli_num_rows($result);
            if ($row_cnt > 0) {
                $data = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $responseArray = array(
                    'hostingData' => $data,
                );
            }
            if ($responseArray) {
                $resultArray = array(

                    "code" => "200",
                    "message" => "Hosting details fetched successfully"
                    ,
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
     * Post/Add sale
     *
     * @param array $data
     * @return multitype:string
     */
    private function createHosting($data, $loginData)
    {

        try {
            $db = $this->dbConnect();
            $validationData = array("hosting_server_type" => $data['hosting_server_type'], "registered_date" => $data['registered_date'], "expired_date" => $data['expired_date'], "email_id" => $data['email_id'], "mobile_no" => $data['mobile_no']);
            $this->validateInputDetails($validationData);
            $validationDateData = array("registered_date" => $data['registered_date'], "expired_date" => $data['expired_date']);
            $this->validateInputDateFormate($validationDateData);

            $dateNow = date("Y-m-d H:i:s");
            $insertQuery = "INSERT INTO tbl_hosting (hosting_server_type,registered_date, expired_date, email_id, mobile_no,status, created_by, created_date) VALUES ('" . $data['hosting_server_type'] . "','" . $data['registered_date'] . "','" . $data['expired_date'] . "','" . $data['email_id'] . "','" . $data['mobile_no'] . "','" . '1' . "','" . $loginData['user_id'] . "','$dateNow')";
            if ($db->query($insertQuery) === true) {
                $db->close();
                $statusCode = "200";
                $statusMessage = "Hosting details created successfully";

            } else {
                $statusCode = "500";
                $statusMessage = "Unable to create Hosting details, please try again later";
            }
            $resultArray = array(

                "code" => $statusCode,
                "message" => $statusMessage

            );
            return $resultArray;
        } catch (Exception $e) {
            return array(
                "apiStatus" => array(
                    "code" => "401",
                    "message" => $e->getMessage()
                ),
            );
        }
    }


    /**
     * Put/Update a Sale
     *
     * @param array $data
     * @return multitype:string
     */
    private function updateHosting($data, $loginData)
    {
        try {
            $db = $this->dbConnect();
            $validationData = array("id" => $data['id'], "hosting_server_type" => $data['hosting_server_type'], "registered_date" => $data['registered_date'], "expired_date" => $data['expired_date'], "email_id" => $data['email_id'], "mobile_no" => $data['mobile_no']);
            $this->validateInputDetails($validationData);
            $dateNow = date("Y-m-d H:i:s");
            $updateQuery = "UPDATE tbl_hosting SET hosting_server_type = '" . $data['hosting_server_type'] . "', registered_date = '" . $data['registered_date'] . "',expired_date = '" . $data['expired_date'] . "', email_id = '" . $data['email_id'] . "', mobile_no = '" . $data['mobile_no'] . "', updated_by = '" . $loginData['user_id'] . "',updated_date = '$dateNow' WHERE id = " . $data['id'] . "";
            if ($db->query($updateQuery) === true) {
                $db->close();
                $statusCode = "200";
                $statusMessage = "Hosting details updated successfully";

            } else {
                $statusCode = "500";
                $statusMessage = "Unable to update Hosting details, please try again later";
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




    private function deleteHosting($data)
    {
        try {
            $id = $data[2];
            $db = $this->dbConnect();
            if (empty ($data[2])) {
                throw new Exception("Bad request");
            }
            $deleteQuery = "UPDATE tbl_hosting set status=0 WHERE id = " . $id . "";
            if ($db->query($deleteQuery) === true) {
                $db->close();
                $statusCode = "200";
                $statusMessage = "Hosting details deleted successfully";

            } else {
                $statusCode = "500";
                $statusMessage = "Unable to delete hosting details, please try again later";
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
            if (empty ($value) || trim($value) == "") {
                throw new Exception($key . " should not be empty!");
            }
        }
    }
    public function validateInputDateFormate($validationDateData)
    {

        foreach ($validationDateData as $key => $value) {
            if ($this->validateDate($value)) {

            } else {
                throw new Exception($key . " invalid date format!");
            }
        }
    }

    function validateDate($date, $format = 'Y-m-d')
    {
        $dateTimeObj = DateTime::createFromFormat($format, $date);
        return $dateTimeObj && $dateTimeObj->format($format) === $date;
    }


    /**
     * Validate function for sale create
     *
     * @param array $data
     * @throws Exception
     * @return multitype:string NULL
     */

    private function getTotalCount($loginData)
    {
        try {
            $db = $this->dbConnect();
            $sql = "SELECT * FROM tbl_hosting WHERE status = 1 and created_by = " . $loginData['user_id'] . "";
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
