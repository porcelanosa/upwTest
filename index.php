<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Peoples on Steps</title>
</head>
<body>
<?php
$db_name           = "upwtest"; // Database name
$db_host           = "localhost";
$db_user           = "root";
$db_pass           = "";
$steps_table_name  = "steps";
$people_table_name = "peoples";
$if_install        = false; // for first run - create and seed tables
try {
    $db = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8",
        $db_user,
        $db_pass,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8', PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
    );

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}

$steps_columns  = ['id', 'step', 'step_name'];
$steps          = [
    ['id' => 1, 'step' => 1, 'step_name' => "First step"],
    ['id' => 2, 'step' => 3, 'step_name' => "Third step"],
    ['id' => 3, 'step' => 2, 'step_name' => "Second step"]
];
$people_columns = ['id', 'step_id'];
$peoples        = [
    ['id' => 1, 'step_id' => 1],
    ['id' => 2, 'step_id' => 2],
    ['id' => 3, 'step_id' => 1],
    ['id' => 4, 'step_id' => 3],
    ['id' => 5, 'step_id' => 2],
    ['id' => 6, 'step_id' => 3],
    ['id' => 7, 'step_id' => 1],
    ['id' => 8, 'step_id' => 2],
    ['id' => 9, 'step_id' => 1],
];
if ((isset($_GET['install']) && $_GET['install'] == 'true') || $if_install) {
    $install_status = true;
    // Create Steps Table
    try {
        $st12 = $db->prepare("DROP TABLE IF EXISTS $steps_table_name; CREATE TABLE IF NOT EXISTS $steps_table_name (id int, step int, step_name varchar(125) );");
        $st12->execute();
        $st12->closeCursor();

    } catch (PDOException $ex) {
        echo $ex->getMessage();
        $install_status = false;
    }
    // Create Peoples Table
    try {
        $st2 = $db->prepare("DROP TABLE IF EXISTS $people_table_name; CREATE TABLE IF NOT EXISTS $people_table_name (id int, step_id int)");
        $st2->execute();
        $st2->closeCursor();

    } catch (PDOException $ex) {
        echo $ex->getMessage();
        $install_status = false;
    }
    // Clear Steps table
    try {
        $st31 = $db->prepare("TRUNCATE $steps_table_name;");
        $st31->execute();
        $st31->closeCursor();

    } catch (PDOException $ex) {
        echo $ex->getMessage();
        $install_status = false;
    }
    // Seeding Steps table
    foreach ($steps as $step) {
        try {
            $id        = $step['id'];
            $step_num  = $step['step'];
            $step_name = $step['step_name'];
            $st31      = $db->prepare("INSERT INTO $steps_table_name(id, step, step_name)  VALUE ($id, $step_num, '$step_name');");
            $st31->execute();
            $st31->closeCursor();

        } catch (PDOException $ex) {
            echo $ex->getMessage();
            $install_status = false;
        }
    }
    // Clear Peoples table
    try {
        $st31 = $db->prepare("TRUNCATE $people_table_name;");
        $st31->execute();
        $st31->closeCursor();

    } catch (PDOException $ex) {
        echo $ex->getMessage();
        $install_status = false;
    }
    // Seeding Peoples table
    foreach ($peoples as $people) {
        try {
            $id      = $people['id'];
            $step_id = $people['step_id'];
            $st41    = $db->prepare("INSERT INTO $people_table_name(id, step_id)  VALUE ($id, $step_id);");
            $st41->execute();
            $st31->closeCursor();

        } catch (PDOException $ex) {
            echo $ex->getMessage();
            $install_status = false;
        }
    }
    if ($install_status) {
        echo '<h2>Install Ok</h2>';
    } else {
        echo '<h2>Install failed</h2>';
    }
}

/**
 * @param $db PDO
 * @param $table_name string
 * @param $columns array
 *
 */
function getArraysFromDB($db, $table_name, $columns = [])
{
    $return_arr     = [];
    $columns_string = '';
    $columnCounts   = count($columns);
    $cycl           = 0;
    foreach ($columns as $column) {
        $columns_string .= $column;
        if ($cycl < $columnCounts - 1) {
            $columns_string .= ", ";
        }
        $cycl++;
    }
    $query = "SELECT $columns_string FROM $table_name";
    try {
        $st = $db->prepare($query);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
        $st->closeCursor();

    } catch (PDOException $ex) {
        echo $ex->getMessage();
    }
    foreach ($result as $row) {
        $row_array = [];
        foreach ($columns as $column) {
            $row_array[$column] = $row[$column];
        }
        $return_arr[] = $row_array;
    }

    return $return_arr;
}

/**
 * @param $steps array
 * @param $peoples array
 *
 * @return array
 */
function getStepsFromArray($steps, $peoples)
{
    $return_arr = [];
    foreach ($steps as $step) {
        $return_arr[$step['id']] = [
            'title' => $step['step_name'],
            'value' => 0
        ];
        foreach ($peoples as $people) {
            if ($step['step'] <= $people['step_id']) {
                $return_arr[$step['id']]['value']++;
            }
        }
    }

    return $return_arr;
}

?>
<style>
    .counts {
        text-align: center;
    }

    table {
        border-collapse: collapse;
        width: 300px;
        border: 1px solid grey
    }

    table th {
        font-weight: bold;
        border: 1px solid grey;
    }

    table tr td {
        border: 1px solid grey
    }
</style>
<h2>Results from arrays</h2>
<table>
    <caption>Steps</caption>
    <tr>
        <th>Step name</th>
        <th class="counts">Peoples</th>
    </tr>
    <? foreach (getStepsFromArray($steps, $peoples) as $step): ?>
        <tr>
            <td><?= $step['title'] ?></td>
            <td class="counts"><?= $step['value'] ?></td>
        </tr>
    <? endforeach; ?>
</table>

<h2>Results from DB</h2>
<table>
    <caption>Steps</caption>
    <tr>
        <th>Step name</th>
        <th class="counts">Peoples</th>
    </tr>
    <?
    $steps_from_db   = getArraysFromDB($db, $steps_table_name, $steps_columns);
    $peoples_from_db = getArraysFromDB($db, $people_table_name, $people_columns);
    ?>
    <? foreach (getStepsFromArray($steps_from_db, $peoples_from_db) as $step): ?>
        <tr>
            <td><?= $step['title'] ?></td>
            <td class="counts"><?= $step['value'] ?></td>
        </tr>
    <? endforeach; ?>
</table>


</body>
</html>