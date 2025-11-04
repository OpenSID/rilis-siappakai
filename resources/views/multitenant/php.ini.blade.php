# PHP Configuration for OpenSID Multi-tenant Site
# Generated for: {{ $kodeDesa }}
# Generated at: {{ $generatedAt }}

; Basic Settings
max_execution_time = {{ $maxExecutionTime ?? 300 }}
max_input_time = {{ $maxInputTime ?? 300 }}
memory_limit = {{ $memoryLimit ?? '256M' }}
post_max_size = {{ $postMaxSize ?? '32M' }}
upload_max_filesize = {{ $uploadMaxFilesize ?? '32M' }}
max_file_uploads = {{ $maxFileUploads ?? 20 }}

; Error Reporting
display_errors = Off
log_errors = On
error_log = {{ $errorLog }}
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Session Configuration
session.save_path = {{ $sessionSavePath }}
session.gc_maxlifetime = {{ $sessionGcMaxlifetime ?? 7200 }}
session.cookie_httponly = On
session.cookie_secure = {{ $sessionCookieSecure ?? 'Off' }}
session.use_strict_mode = On
session.sid_length = 48
session.sid_bits_per_character = 6

; File Upload Settings
upload_tmp_dir = {{ $uploadTmpDir }}
file_uploads = On

; Security Settings
expose_php = Off
allow_url_fopen = {{ $allowUrlFopen ?? 'Off' }}
allow_url_include = Off
disable_functions = {{ $disableFunctions ?? 'exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source' }}

; Date/Timezone
date.timezone = {{ $timezone ?? 'Asia/Jakarta' }}

; OpenSSL Configuration
openssl.cafile = /etc/ssl/certs/ca-certificates.crt
openssl.capath = /etc/ssl/certs

; MySQL Configuration
mysqli.default_socket = /var/run/mysqld/mysqld.sock
pdo_mysql.default_socket = /var/run/mysqld/mysqld.sock

; Output Buffering
output_buffering = 4096
zlib.output_compression = Off

; Resource Limits
max_input_vars = {{ $maxInputVars ?? 3000 }}
max_input_nesting_level = {{ $maxInputNestingLevel ?? 64 }}

; OPcache Settings (if available)
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1

; Additional Security
auto_prepend_file = 
auto_append_file = 
default_mimetype = "text/html"
default_charset = "UTF-8"

; Extensions (Common ones needed for OpenSID)
extension = mysqli
extension = pdo_mysql
extension = curl
extension = gd
extension = mbstring
extension = xml
extension = zip
extension = json
extension = iconv
extension = ctype
extension = fileinfo
extension = openssl
extension = tokenizer