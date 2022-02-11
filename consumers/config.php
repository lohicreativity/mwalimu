<?php
date_default_timezone_set('Africa/Dar_es_Salaam');
define('SPCODE', 'SP412');
define('SP_NUMBER', '1002');
define('SPSYSID', 'LMNMA002');
define('SERVER', 'http://154.118.230.202:80/');
#define('SERVER', 'http://10.1.1.18:80/');
define('POST_BILL_PATH', 'api/bill/qrequest');
define('POST_SIGNED_BILL_PATH', 'api/bill/sigqrequest');
define('RECONCILE_PATH', 'api/reconciliations/sig_sp_qrequest');
define('SIGNED_RECONCILE_PATH', 'api/reconciliations/sig_sp_qrequest');
define('RABBIT_HOST', '127.0.0.1');
define('RABBIT_PORT', 5672);
define('RABBIT_USER', 'mnmamq');
define('RABBIT_PASS', 'mnma@123');
define('GEPG_STS_SUCCESS', 7101);
# Number of retries, in case of failure
define('RETRY_COUNT', 4); //70
# Retry Interval in Milli-Seconds
define('RETRY_INTERVAL', 15000); //300000
define('SP_SERVER', 'http://41.59.91.194/');
define('SP_BILL_PATH', 'payment/bills');
define('SP_RECEIPT_PATH', 'payment/receipt');
define('SP_RECON_PATH', 'payment/reconciliation');
/*Certificates*/
define('CERT_PASSWORD', 'MnmaPassword');
define('RECON_DATA_TAG', 'gepgSpReconcReqAck');
define('DATA_TAG', 'gepgBillSubReqAck');
define('SIGN_TAG', 'gepgSignature');
define('PUBLIC_CERT_PASSWORD','S3R1KAL1');

