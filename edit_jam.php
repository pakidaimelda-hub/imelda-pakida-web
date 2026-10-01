<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
        integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
</head>

<body>
    <br>
    <div class="row col-12">
        <div class="col-3">
            <div class="container">
                <ul class="list-group">
                    <?php include 'menu.php'; ?>
                </ul>
            </div>
        </div>
        <div class="col-9">
            <div class="container">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title">Edit Jam</h1>
                    </div>
                </div>
                <br>
                <?php
                    $koneksi = mysqli_connect("localhost", "root", "", "jadwal");
                    $id_jam = $_GET['id_jam'];
                    $jam = mysqli_query($koneksi, "select * from jam where id_jam='$id_jam'");
                    $jam = mysqli_fetch_array($jam);
                ?>
                <form method="post" action="proses_edit_jam.php">
                    <input type="hidden" name="id_jam" value="<?php echo $id_jam; ?>">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Jam</label>
                        <input type="number" class="form-control" name="jam" value="<?php echo $jam['jam']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Mulai</label>
                        <input type="time" class="form-control" name="mulai"  value="<?php echo $jam['mulai']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">akhir</label>
                        <input type="time" class="form-control" name="akhir"  value="<?php echo $jam['akhir']; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Edit</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous">
    </script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js">
    </script>

    <!-- fungsi datatable -->
    <script>
        $(document).ready(function () {
            $('#table_id').DataTable();
        });

    </script>
</body>

</html>
