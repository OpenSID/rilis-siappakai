# OpenLiteSpeed Virtual Host Template for OpenSID Multi-tenant
# Generated for: {{ $kodeDesa }}
# Domain: {{ $domain }}
# Generated at: {{ $generatedAt }}

docRoot                   {{ $documentRoot }}
index                     index.php, index.html, index.htm
vhAliases                 

errorlog                  {{ $errorLog }} {
  useServer               0
  logLevel                {{ $logLevel }}
  rollingSize             10M
  keepDays                30
  compressArchive         1
}

accesslog                 {{ $accessLog }} {
  useServer               0
  logFormat               combined
  logHeaders              5
  rollingSize             10M
  keepDays                30
  compressArchive         1
}

index  {
  useServer               0
  indexFiles              index.php, index.html, index.htm
  autoIndex               0
  autoIndexURI            /_autoindex/default.php
}

errorpage 404 {
  url                     /error404.html
}

expires  {
  enableExpires           1
  expiresByType           image/*=A604800,text/css=A604800,application/x-javascript=A604800,application/javascript=A604800,font/*=A604800,application/x-font-ttf=A604800
}

scripthandler  {
  add                     lsphp{{ $phpVersion }} php
}

phpIniOverride  {
{{ $phpIniOverride }}
}

rewrite  {
  enable                  1
  autoLoadHtaccess        1
  logLevel                0
}

vhssl  {
  keyFile                 {{ $sslKey }}
  certFile                {{ $sslCert }}
  protocol                All
  ciphers                 EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH
  enableECDHE             Yes
  renegProtection         Yes
  sslSessionCache         Yes
  enableSpdy              Yes
  enableQuic              Yes
}