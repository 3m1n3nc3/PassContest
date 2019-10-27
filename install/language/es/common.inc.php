<?php
//------------------------------------------------------------------------------ 
//*** Spanish (es)
//------------------------------------------------------------------------------ 

$arrLang = array();

$arrLang['alert_admin_email_wrong'] = "Email administrador tiene formato incorrecto! Vuelva a inscribir.";
$arrLang['alert_min_version_db'] = "Este programa requiere al menos la versión de _DB_VERSION_ _DB_ instalado (versión actual es _DB_CURR_VERSION_). No se puede continuar con la instalación.";
$arrLang['alert_min_version_php'] = "Este programa requiere al menos _PHP_VERSION_ versión de PHP instalada (versión actual es _PHP_CURR_VERSION_). Usted no puede continuar la instalación.";
$arrLang['alert_directory_not_writable'] = "El directorio <b>_FILE_DIRECTORY_</b> no se puede escribir! <br />Debe conceder 'escribe' permisos (0755 derechos de acceso o 777, dependiendo de la configuración del sistema) a este directorio antes de iniciar la instalación!";
$arrLang['alert_extension_not_installed'] = "Requerido extensión pdo_".EI_DATABASE_TYPE." no está instalado en el servidor! No se puede continuar con la instalación.";
$arrLang['alert_unable_to_install'] = "No se puede instalar esta aplicación, ya que una aplicación con la misma identidad ya está instalado. <br>Sólo puede <b>Actualización</b> o <b>Desinstalar</b> que. Asegúrese de tener una copia de seguridad de su base de datos antes de continuar.";
$arrLang['alert_required_fields'] = "Los campos marcados con un asterisco son obligatorios";
$arrLang['alert_http_server_empty'] = "¡HTTP SERVER no puede estar vacío! Vuelva a ingresar.";
$arrLang['alert_site_dir_empty'] = "Site Root Directory no puede estar vacío. Vuelva a ingresar.";
$arrLang['alert_db_host_empty'] = "Acogida de base de datos no puede estar vacío! Por favor, vuelva a entrar.";
$arrLang['alert_db_name_empty'] = "Nombre de base de datos no puede estar vacío! Por favor, vuelva a entrar.";
$arrLang['alert_db_username_empty'] = "Nombre de usuario de base de datos no puede estar vacío! Por favor, vuelva a entrar.";
$arrLang['alert_db_password_empty'] = "Contraseña de base de datos no puede estar vacío! Por favor, vuelva a entrar.";
$arrLang['alert_admin_username_empty'] = "Nombre de usuario administrador no puede estar vacío! Por favor, vuelva a entrar.";
$arrLang['alert_admin_password_empty'] = "Contraseña de administrador no puede estar vacío! Por favor, vuelva a entrar.";
$arrLang['alert_wrong_testing_parameters'] = "Los parámetros de prueba están equivocados! Por favor, introduzca los parámetros válidos.";
$arrLang['alert_remove_files'] = "Por razones de seguridad, por favor, elimine <b>start.php</b> de archivos e <b>instalar/</b> carpeta de su servidor!";
$arrLang['alert_wrong_parameter_passed'] = "Parámetro no válido pasó! Por favor, volver al paso anterior y vuelva a intentarlo.";

$arrLang['error_asp_tags'] = "Este script requiere ajustes etiquetas ASP en ON.";
$arrLang['error_can_not_open_config_file'] = "Base de datos se ha creado correctamente! No se puede abrir el archivo de configuración _CONFIG_FILE_PATH_ para guardar información.";
$arrLang['error_can_not_read_file'] = "No se pudo leer _SQL_DUMP_FILE_ archivo! Por favor, compruebe si existe un archivo.";
$arrLang['error_check_db_exists'] = "Base de datos de conexión de error! Por favor, compruebe si su base de datos existe y el acceso permitido para el usuario <b>_DATABASE_USERNAME_</b>._ERROR_<br />";
$arrLang['error_check_db_connection'] = "Base de datos de conexión de error! Por favor, compruebe su conexión parameters._ERROR_<br />";
$arrLang['error_pdo_support'] = "Este script requiere la extensión PDO instalado.";
$arrLang['error_sql_executing'] = "SQL de ejecución de error! Por favor, Activar el modo de depuración y comprobar cuidadosamente la sintaxis de su archivo de volcado de SQL.";
$arrLang['error_server_requirements'] = "Esta configuración requiere Installation _SETTINGS_NAME_ encendidos/instalado.";
$arrLang['error_vd_support'] = "Este script requiere el apoyo del directorio virtual en ON.";

$arrLang['admin_access_data'] = "Los datos de acceso de administrador";
$arrLang['admin_access_data_descr'] = "(que lo necesitan para entrar en el área de administración protegida)";
$arrLang['admin_email'] = "Email Administrador";
$arrLang['admin_email_info'] = "Email administración que será reemplazado en el volcado SQL con marcador de correo electrónico (si está definida).";
$arrLang['mc_api'] = "Mail Chimp API";
$arrLang['mc_api_info'] = "Guardar direcciones en la lista de correo de mailchimp, puede obtener su clave de API de su mailchimp, obtenga uno aquí <a href='http://admin.mailchimp.com/account/api/'> http://admin.mailchimp.com/account/api </a> ";
$arrLang['mc_listid'] = "Nombre de lista de Chimpancé de correo";
$arrLang['mc_listid_info'] = "Ingrese su identificación única de la lista de Mail Chimp, cree una lista aquí <a href='http://admin.mailchimp.com/lists/'> http://admin.mailchimp.com/lists </a> ";
$arrLang['admin_login'] = "Entrada Admin";
$arrLang['admin_login_info'] = "Su nombre de usuario debe tener al menos 6 caracteres de longitud y entre mayúsculas y minúsculas. Por favor, no introduzca caracteres acentuados.";
$arrLang['admin_password'] = "Contraseña de administrador";
$arrLang['admin_password_info'] = "Le recomendamos que su contraseña no es una palabra que puede encontrar en el diccionario, incluye capital y minúsculas, y contiene por lo menos un carácter especial (1-9,!, *, _, Etc).";
$arrLang['administrator_account'] = "Cuenta de administrador";
$arrLang['administrator_account_skipping'] = "Salto (Cuenta de administrador no es necesario)";
$arrLang['asp_tags'] = "Asp Etiquetas";
$arrLang['back'] = "Espalda";
$arrLang['build_date'] = "Fecha de compilación";
$arrLang['cancel_installation'] = "Cancelar la instalación";
$arrLang['click_start_button'] = "Haga clic en el botón Start para continuar";
$arrLang['click_to_start_installation'] = "Haga clic para iniciar la instalación";
$arrLang['checked'] = "Comprobar";
$arrLang['complete'] = "Completar";
$arrLang['complete_installation'] = "Instalación completa";
$arrLang['completed'] = "Terminado";
$arrLang['continue'] = "Continuar";
$arrLang['continue_installation'] = "Continuar instalación";
$arrLang['database_extension'] = "Extensión de base de datos";
$arrLang['http_server'] = "Base URL";
$arrLang['http_server_info'] = "La URL base de su sitio web excluyendo el protocolo E.g .: www.newnify.com o www.newnify.com/whatafolio o whataportal.newnify.com o localhost/whatafolio";
$arrLang['site_dir'] = "Directorio raíz";
$arrLang['site_dir_info'] = "Para la mayoría de los hosts, el directorio raíz de su dominio principal es la carpeta 'public_html'. Para los dominios addon (sitios web separados) sería 'public_html/newnify.com' y para los subdominios (como whataportal.newnify.com) sería 'public_html/whatafolio'. Para algunos hosts, usted podría estar buscando algo que se parezca a: /home/cpanelusername/public_html/ currentsite. Si se encuentra en un servidor local, se verá como 'C:/wamp64 /www/whatafolio'";
$arrLang['database_host'] = "Base de datos de host";
$arrLang['database_host_info'] = "Nombre de host o dirección IP del servidor de base de datos. El servidor de base de datos puede ser en forma de un nombre de host (y / o dirección de puerto), como db1.myserver.com, o localhost:5432, o como una dirección IP, como 192.168.0.1";
$arrLang['database_import'] = "Base de datos de importación";
$arrLang['database_import_error'] = "Base de datos de importación (de error)";
$arrLang['database_name'] = "Nombre de base de datos";
$arrLang['database_name_info'] = "Nombre de base de datos. La base de datos utilizada para almacenar los datos. Un ejemplo de nombre de la base es 'testdb'.";
$arrLang['database_username'] = "Base de datos de usuario";
$arrLang['database_username_info'] = "Nombre de usuario de base de datos. El nombre de usuario utilizado para conectarse al servidor de base de datos. Un ejemplo de nombre de usuario es 'test_123'.";
$arrLang['database_password'] = "Base de datos Contraseña";
$arrLang['database_password_info'] = "Contraseña de base de datos. La contraseña se utiliza junto con el nombre de usuario, que forma la cuenta de usuario de base de datos.";
$arrLang['database_prefix'] = "Base de datos de prefijo (opcional)";
$arrLang['database_prefix_info'] = "Prefijo de la base de datos. Se utiliza para definir el prefijo único para las tablas de base de datos y prevenir un tipo de datos de interferir con el otro. Un ejemplo de prefijo de la base de datos es 'abc_'.";
$arrLang['database_settings'] = "Propiedades de la base";
$arrLang['directories_and_files'] = "Directorios y archivos";
$arrLang['disabled'] = "con discapacidad";
$arrLang['enabled'] = "habilitado";
$arrLang['error'] = "Error";
$arrLang['extensions'] = "Extensiones";
$arrLang['getting_system_info'] = "Obtener información del sistema";
$arrLang['file_successfully_rewritten'] = "El archivo _CONFIG_FILE_ éxito fue re-escrito y base de datos actualizada.";
$arrLang['file_successfully_deleted'] = "El archivo _CONFIG_FILE_ se ha eliminado correctamente y eliminar la base de datos.";
$arrLang['file_successfully_created'] = "El archivo _CONFIG_FILE_ se ha creado correctamente.";
$arrLang['failed'] = "fracasado";
$arrLang['folder_paths'] = "Ordner Pfade";
$arrLang['follow_the_wizard'] = "Siga el <b>Asistente</b> para instalar el programa";
$arrLang['installed'] = "instalado";
$arrLang['installation_completed'] = "La instalación ha finalizado!";
$arrLang['installation_guide'] = "Guía de instalación";
$arrLang['installation_type'] = "Tipo de instalación";
$arrLang['language'] = "Lengua";
$arrLang['license'] = "Licencia";
$arrLang['loading'] = "de carga";
$arrLang['mbstring_support'] = "Cadena multibyte apoyar";
$arrLang['magic_quotes_gpc'] = "Comillas Mágicas de (Get/Post/Cookie)";
$arrLang['magic_quotes_runtime'] = "Cotizaciones en tiempo de ejecución Magic";
$arrLang['magic_quotes_sybase'] = "Comillas Mágicas Sybase están en estilo";
$arrLang['mode'] = "Modus";
$arrLang['modes'] = "Modos";
$arrLang['new_installation_of'] = "Nueva instalación de";
$arrLang['new'] = "Nuevo";
$arrLang['no'] = "No";
$arrLang['no_writable'] = "no puede escribir";
$arrLang['not_installed'] = "no se instala";
$arrLang['off'] = "De";
$arrLang['ok'] = "OK";
$arrLang['on'] = "En";
$arrLang['passed'] = "pasado";
$arrLang['password_encryption'] = "Contraseña de cifrado";
$arrLang['perform_manual_installation'] = "Realizar un <b>manual</b> de Instalación";
$arrLang['pdo_support'] = "Soporte PDO";
$arrLang['php_version'] = "PHP Versión";
$arrLang['proceed_to_login_page'] = "Proceda a la página de acceso";
$arrLang['ready_to_install'] = "Preparado para la instalación";
$arrLang['remove_configuration_button'] = "Quite la configuración y volver a empezar";
$arrLang['required_php_settings'] = "Ajustes PHP requeridos";
$arrLang['safe_mode'] = "Modo seguro";
$arrLang['select_installation_language'] = "Selecciona Idioma de instalación";
$arrLang['select_installation_type'] = "Seleccione el tipo de instalación";
$arrLang['sendmail_from'] = "De Sendmail";
$arrLang['sendmail_path'] = "Sendmail Camino";
$arrLang['server_api'] = "Servidor de la API";
$arrLang['server_requirements'] = "Requisitos del servidor";
$arrLang['session_support'] = "Sesión de Soporte";
$arrLang['short_open_tag'] = "Etiqueta abierta a corto";
$arrLang['smtp'] = "SMTP";
$arrLang['smtp_port'] = "Puerto SMTP";
$arrLang['start'] = "Iniciar";
$arrLang['start_all_over'] = "Empezar todo de";
$arrLang['start_all_over_text'] = "Si usted desea eliminar esta instalación por algún motivo, puede forzar al instalador para eliminar la configuración actual y empezar todo de nuevo. <br><b>ADVERTENCIA</b>: Hay que deshacer la instalación de bases de datos de forma manual para eliminar todos los cambios que se hicieron.";
$arrLang['step_1_of'] = "Paso 1 de 6";
$arrLang['step_2_of'] = "Paso 2 de 6";
$arrLang['step_3_of'] = "Paso 3 de 6";
$arrLang['step_4_of'] = "Paso 4 de 6";
$arrLang['step_5_of'] = "Paso 5 de 6";
$arrLang['step_6_of'] = "Paso 6 de 6";
$arrLang['sub_title_message'] = "Este asistente le guiará a través de todo el proceso de instalación";
$arrLang['system'] = "Sistema de";
$arrLang['system_architecture'] = "Arquitectura del sistema";
$arrLang['test_connection'] = "Conexión de prueba";
$arrLang['test_database_connection'] = "Prueba de conexión de base de datos";
$arrLang['unknown'] = "Desconocida";
$arrLang['uninstall'] = "Desinstalar";
$arrLang['uninstallation_completed'] = "Desinstalación completada!";
$arrLang['update'] = "Actualizar";
$arrLang['updating_completed'] = "Actualización completada!";
$arrLang['virtual_directory_support'] = "Directorio Virtual de Apoyo";
$arrLang['we_are_ready_to_installation'] = "We are now ready to proceed with installation";
$arrLang['we_are_ready_to_installation_text'] = "En este asistente de configuración paso será tratar de crear todas las tablas de base de datos necesarios y llenar con datos. Si algo va mal, vuelve a las Propiedades de la base paso y hacer que cada información que has introducido no es correcto.";
$arrLang['writable'] = "escritura";

?>