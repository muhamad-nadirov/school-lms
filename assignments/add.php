<?php
require_once("../db.php");


$query= "SELECT * FROM groups WHERE ORDER BY name";
$result=mysqli_query($conn, $query);

?>

<div class="page-header">
    <h1 class="page-header-title">Добавить задания</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="assignments/insert.php" method="post">
            <div class="mb-4">
                <label for="form-label"></label>
                <select name="group_id" class="form-select" id="">
                    <?php
                    
                    while($result=mysqli_fetch_assoc($row)){
                    ?>

                    <option value="<?= $assignment['id']?>"><?= $assignment['name']?></option>

                    <?php    
                    }
                    
                    ?>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="assignments/insert.php" method="post">
            <div class="mb-4">
                <label for="form-label"></label>
                <input type="text" class="form-control">
            </div>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-body">
        <form action="assignments/insert.php" method="post">
            <div class="mb-4">
                <label for="form-label"></label>
                <input type="text" class="form-control">
            </div>
        </form>
    </div>
</div>

