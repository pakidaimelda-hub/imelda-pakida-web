<?php
    
    error_reporting(0);
    session_start();

    $koneksi = mysqli_connect("localhost", "root", "", "jadwal");

    $id_jadwal      = $_POST['id_jadwal'];
    $id_guru        = $_POST['id_guru'];
    $id_kelas       = $_POST['id_kelas'];
    $id_jam         = $_POST['id_jam'];
    $id_pelajaran   = $_POST['id_pelajaran'];
    $hari           = $_POST['hari'];

    // jika tidak ada id_guru
    if(empty($id_guru)){

        //cek data mata pelajaran sesuai id_jadwal
        $select         = "SELECT COUNT(*) as total FROM jadwal WHERE id_jadwal='$id_jadwal' ";
        $select         = mysqli_query($koneksi, $select);
        $select         = mysqli_fetch_array($select);

        //jika data mata pelajaran sudah ada maka edit
        if($select['total'] > 0){
            mysqli_query($koneksi, "update jadwal set id_pelajaran='$id_pelajaran', id_jam='$id_jam', hari='$hari' where id_jadwal='$id_jadwal' ");

        //atau jika tidak ada maka insert baru
        }else{
            mysqli_query($koneksi, "insert into jadwal set id_pelajaran='$id_pelajaran', id_jam='$id_jam', hari='$hari', id_kelas='$id_kelas'");
        }

    }else{
        
        //jika id_jadwal belum ada
        if(empty($id_jadwal)){

            //jika gagal set session gagal
            $_SESSION["gagal"] = "isi terlebih dahulu mata pelajaran";
        }else{

            //cek data
            $select2         = "SELECT COUNT(*) as total FROM jadwal 
                                JOIN kelas on kelas.id_kelas = jadwal.id_kelas
                                JOIN semester on semester.id_semester = kelas.id_semester
                                WHERE id_guru='$id_guru' && id_jam='$id_jam' && hari='$hari' && semester.status='1'
                                ";
            $select2         = mysqli_query($koneksi, $select2);
            $select2         = mysqli_fetch_array($select2);

            //jika data guru sudah ada di hari + jam + id_guru di kelas lain
            if($select2['total'] > 0){

                $get_data         = mysqli_query($koneksi, 
                                    "select * from jadwal 
                                    join kelas on kelas.id_kelas = jadwal.id_kelas
                                    join semester on semester.id_semester = kelas.id_semester
                                    WHERE id_guru='$id_guru' && id_jam='$id_jam' && hari='$hari'
                                    "
                                    );
                $cek_data         = mysqli_fetch_array($get_data);

                $_SESSION["gagal"] = 'Guru Sudah Mengisi di Kelas '.$cek_data['kelas'].' '.$cek_data['tahun'].' '.$cek_data['semester'];
            }else{
                mysqli_query($koneksi, "update jadwal set id_guru='$id_guru' where id_jadwal='$id_jadwal' ");
            }
        }
    }
    header('Location: detail_jadwal.php?id_kelas='.$id_kelas);   
?>