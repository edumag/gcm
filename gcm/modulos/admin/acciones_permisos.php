<?php

/**
 * @file acciones_permisos.php
 * @brief Definimos la lisa blanca para los usuarios basicos
 *
 * $acciones[módulo][acción][] = rol;
 *
 */

/**
 * Configuración para los permisos de usuario
 */

$acciones['admin']['presentar_menu_administrativo'][] = 'usuario';
$acciones['admin']['ejecutar_tests_modulos']       [] = 'usuario';
$acciones['admin']['perfil_usuario']               [] = 'usuario';
$acciones['admin']['activar_tema_admin']           [] = 'usuario';
$acciones['admin']['confirmar_configuracion']      [] = 'usuario';

$acciones['imagenes']['formulario'][] = 'usuario';

$acciones['temas']['administrar'][] = 'usuario';
