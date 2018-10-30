<?php
include "../settings.php";
// 创建数据库
function CreateDatabase($databaseName, $collationName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
    $success = false;
    $msg = '';
    if($con->connect_errno) {
        $msg = $con->connect_error;
    } else if($con->select_db($databaseName)) {
        $msg = '数据库'.$databaseName.'已存在！';
    } else {
        $sql = 'CREATE DATABASE '.$databaseName.' COLLATE '.$collationName;
        $con->query($sql);
        if($con->error)
            $msg = $con->error;
        else {
            $success = true;
            $msg = '数据库'.$databaseName.'创建成功！';
        }
    }
    $con->close();
    return json_encode(array('success'=>$success, 'msg'=>$msg));
}

// 删除数据库
function DeleteDatabase($databaseName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
    $success = false;
    $msg = '';
    $system = array('information_schema', 'mysql', 'performance_schema', 'sys');
    if(in_array($databaseName,$system))
        $msg = '无法删除系统表！';
    else if($con->errno)
        $msg = $con->error;
    else {
        if(!$con->select_db($databaseName)) {
            $msg = '数据库'.$databaseName.'不存在！';
        } else {
            $sql = 'DROP DATABASE '.$databaseName;
            $con->query($sql);
            if($con->error)
                $msg = $con->error;
            else
            {
                $success = true;
                $msg = '数据库'.$databaseName.'删除成功！';
            }
        }

    }
    $con->close();
    return json_encode(array('success'=>$success, 'msg'=>$msg));
}

function CreateTable($databaseName, $tableName, $data)
{
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;

    if ($con->connect_errno)
        $msg = $con->connect_error;
    else if (!$con->select_db($databaseName))
        $msg = '不存在数据库' . $databaseName . '!';
    else {
        $result = $con->query("SHOW TABLES LIKE '" . $tableName . "'");
        if($con->errno)
            $msg = $con->error;
        else if ($result->num_rows == 1)
            $msg = '数据库' . $databaseName . '已存在表' . $tableName . '!';
        else {
            $data = explode(',', $data);
            $key = array();
            $countColumns = count($data) / 5;
            $sql = 'CREATE TABLE ' . $tableName . '(';
            for ($i = 0; $i < $countColumns; $i++) {
                $name = $data[$i * 5];
                $type = $data[$i * 5 + 1];
                $size = $data[$i * 5 + 2];
                $notNull = $data[$i * 5 + 3];
                $isKey = $data[$i * 5 + 4];

                $sql .= $name . ' ' . $type;
                if (!empty($size))
                    $sql .= '(' . $size . ')';
                if (!strcmp($notNull, 'true'))
                    $sql .= ' NOT NULL';
                if (!strcmp($isKey, 'true'))
                    array_push($key, $name);
                if (!(($i == $countColumns - 1) && count($key) == 0))
                    $sql .= ',';
            }
            if (count($key) != 0) {
                $sql .= 'PRIMARY KEY(';
                foreach ($key as $index => $value) {
                    $sql .= $value;
                    if ($index != count($key) - 1)
                        $sql .= ',';
                }
                $sql .= ')';
            }

            $sql .= ')ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $result = $con->query($sql);
            if($con->errno)
                $msg = $con->error;
            else {
                $success = true;
                $msg = '数据表'.$tableName.'创建成功！';
            }
        }
    }
    $con->close();
    return json_encode(Array('success'=>$success, 'msg'=>$msg));
}

function GetDatabaseDetail($databaseName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;
    $data = array();
    global $path;

    $colHeaders = array('','表名','记录数');
    $columns = array(array('type'=>'text','className'=>'htCenter htMiddle','width'=>20, 'renderer'=>'html'),array('type'=>'text','className'=>'htCenter htMiddle', 'width'=>100, 'renderer'=>'html'),array('type'=>'text','className'=>'htCenter htMiddle', 'width'=>25));
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = "SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.tables WHERE TABLE_SCHEMA='$databaseName'";
        $result = $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $temp = array();
            while($row = $result->fetch_assoc()) {
                $tableName = $row['TABLE_NAME'];
                array_push($temp, $tableName);
            }
            $result->free_result();
            if(count($temp) == 0) {
                $success = true;
                array_push($temp,array('','',''));
            } else
                foreach ($temp as $value) {
                    $result = $con->query('SELECT COUNT(*) FROM '.$databaseName.'.'.$value);
                    if($con->errno)
                      $msg = $con->error;
                    else {
                        $success = true;
                        if ($row = $result->fetch_assoc())
                            array_push($data, array('<a href="javascript:void(0)" class="delete-table delete" tb="'.$value.'">删除</a>','<a href="'.$path.'view-edit-table?db='.$databaseName.'&tb='.$value.'" class="access " title="访问数据表 '.$value.'">'.$value.'</a>', $row['COUNT(*)']));
                    }
                }
        }
    }
    $con->close();
    return $success?json_encode(array('success'=>$success,'colHeaders'=>$colHeaders,'columns'=>$columns,'data'=>$data)):json_encode(array('success'=>$success,'msg'=>$msg));
}

function DeleteTable($databaseName, $tableName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $con->query('DROP TABLE '.$databaseName.'.'.$tableName);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            $msg = '数据表'.$tableName.'成功删除！';
        }
    }
    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}


function GetTablesList($databaseName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);


    $success = false;
    $msg = '';

    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $success = true;
        $result = $con->query('SHOW TABLES FROM '.$databaseName);
        while($row = $result->fetch_assoc()) {
            $value = $row[sprintf('Tables_in_%s',$databaseName)];
            $msg .= '<option value="' . $value . '">'.$value.'</option>';
        }
        $result->free_result();
    }

    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}

function LoadTableData($databaseName, $tableName, $page = 1,$limit = 25) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);

    $success = false;
    $msg = null;
    $data = array();

    $colHeaders = array('','');
    $columnsName = array();
    $columns = array(array('type'=>'text','className'=>'htCenter htMiddle','width'=>70, 'renderer'=>'html'),array('type'=>'text','className'=>'htCenter htMiddle','width'=>70, 'renderer'=>'html'));
    $key = array();
    $totalPage = 0;

    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = "SHOW COLUMNS FROM $databaseName.$tableName";
        $result = $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            while($row = $result->fetch_assoc()) {
                $columnTemp = array('type'=>'text','className'=>'htCenter htMiddle', 'renderer'=>'html');
                $colHeaderTemp = $row['Field'];
                array_push($colHeaders,$colHeaderTemp);
                array_push($columns,$columnTemp);
                array_push($columnsName, $colHeaderTemp);
            }
            $sql = "SELECT * FROM $databaseName.$tableName LIMIT ".($page - 1)*$limit.','.$limit;
            $result = $con->query($sql);
            if($con->errno) {
                $success = false;
                $msg = $con->error;
            } else {
                $success = true;
                if($result->num_rows == 0) {
                    $list = array('<del>null</del>','<del>null</del>');
                    foreach ($columnsName as $item) {
                        array_push($list, '<del>null</del>');
                    }
                    array_push($data,$list);
                }
                else {
                    $count = 0;
                    while($row = $result->fetch_assoc()) {

                        $list = array('<a>编辑</a>',"<a href='javascript:void(0)' class='deleteData'column='$count'>删除</a>");
                        foreach ($columnsName as $item) {
                            $value = ($row[$item] == null)?'<del>null</del>':$row[$item];
                            array_push($list, $value);
                        }
                        array_push($data,$list);
                        $count ++;
                    }
                    $result = $con->query("SELECT COUNT(*) FROM $databaseName.$tableName");
                    if($row = $result->fetch_assoc())
                        $totalPage = ceil(intval($row['COUNT(*)'])/$limit);
                }

            }
            $result->free_result();
        }
    }
    $con->close();
    if($totalPage == 0)
        $page = 0;
    return $success?json_encode(array('success'=>$success,'colHeaders'=>$colHeaders,'columns'=>$columns,'data'=>$data,'totalPage'=>$totalPage,'currentPage'=>$page)):json_encode(array('success'=>$success,'msg'=>$msg));
}

function DeleteTableData($databaseName, $tableName, $condition) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = "DELETE FROM $databaseName.$tableName WHERE $condition";
        $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            $msg = '删除成功！';
        }
    }
    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}

function GetDatabases() {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
    $success = false;
    $msg = '';
    $data = array();
    global $path;
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = 'SELECT SCHEMA_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA;';
        $result = $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            while($row = $result->fetch_assoc()) {
                array_push($data, array('<a href="javascript:void(0)" class="deleteDatabase delete" db="'.$row['SCHEMA_NAME'].'">删除</a>',"<a href='".$path."new-delete-table?db=".$row['SCHEMA_NAME']."' title='访问数据库 ".$row['SCHEMA_NAME']."' class='access'>".$row['SCHEMA_NAME']."</a>",$row['DEFAULT_COLLATION_NAME']));
            }
        }
    }
    return json_encode(array('success'=>$success,'msg'=>$msg,'data'=>$data));
}