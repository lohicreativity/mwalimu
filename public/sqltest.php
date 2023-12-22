<?php
$serverName="41.59.91.198";
$connectionOptions=[
	"Database"=>"ARMSIntegration",
	"Uid"=>"armsuser",
	"Encrypt"=>"no",
	"TrustServerCertificate"=>"yes",
	"PWD"=>"arms2o23!"
];

$conn=sqlsrv_connect ($serverName,$connectionOptions);

if($conn==false)
   die(print_r(sqlsrv_errors(),true));
 else echo 'Connection Success';

// $sql="INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES 
  // ('D','CRDB','REC03','10','TF','MNMA003','TEST','INV003','100.0','C','10')";
//$sql = "INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,IDAUTOCASH,IDBILLCYCL,IDSVCCHRG,IDDLNQ,CODECURN,EMAIL1,EMAIL2) VALUES ('BDED485922','44322','SHOBOLE, JOVITH ','P.O Box 27,Simiyu','ARUMERU','BANG','Unknown','Tanzania','Tanzania','P.O Box 27,Simiyu','Tanzania','Jones, Shobole Nyombi','255753690473','0787691417','BD.ED','STD','NIL','NIL','NIL','NIL','TSH','dennis.lupiana@gmail.com','UNKNOWN')";

//$sql = "INSERT INTO invoices(INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('994120231954','2021','Miscellaneous Income','BDED485922','SHOBOLE, JOVITH ','1','12','Miscellaneous Income','Miscellaneous Income','500.00','0','2022')";

//$sql = "INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,IDAUTOCASH,IDBILLCYCL,IDSVCCHRG,IDDLNQ,CODECURN,EMAIL1,EMAIL2) VALUES ('BDED485922','44322','SHOBOLE, JOVITH ','P.O Box 27,Simiyu','ARUMERU','BANG','Unknown','Tanzania','Tanzania','P.O Box 27,Simiyu','Tanzania','Jones, Shobole Nyombi','255753690473','0787691417','BD.ED','STD','NIL','NIL','NIL','NIL','TSH','dennis.lupiana@gmail.com','UNKNOWN')";
//$results=sqlsrv_query($conn,$sql) or die( print_r( sqlsrv_errors(), true));

//$sql = "INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('615','NMB','32342','20220212','Application','33243','KIMOLLO','13344324','1000442498','0','20221203')";
//$results=sqlsrv_query($conn,$sql) or die( print_r( sqlsrv_errors(), true));
$sql = "SELECT * FROM receipts";

 $results=sqlsrv_query($conn,$sql) or die( print_r( sqlsrv_errors(), true));

 //validate data insertion
 if($results){
         print_r($results);
        while($row = sqlsrv_fetch_array($results)){
          print_r($row);
        }
	echo " Data insertion Success";
}else{
	echo "Data Insertion ERROR";
}

?> 
