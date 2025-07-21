<?php
$host = 'aws.connect.psdb.cloud';
$port = 3306;
echo @fsockopen($host, $port) ? 'Conexão permitida' : 'Conexão bloqueada';