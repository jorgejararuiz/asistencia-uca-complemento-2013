asistencia-uca-complemento-2013
===============================

Trabajo práctico de Complemento de Informática 2013 / Asistencia Web

*Cosas que tienen que tener en cuenta para configurar el proyecto:
Con XAMPP hay que configurar el Vhost (XAMPP/xamppfiles/etc/extra/httpd-vhosts.conf es en Mac), hay que pegar esto:
OJO con DocumentRoot y Directory, si tienen en otro lugar eso tienen que cambiar.

------------------------------------------------------------------------------------------------------
<VirtualHost *:80>
   ServerName local.uca.edu.py
   SetEnv APPLICATION_ENV development
   ServerName local.uca.edu.py
   DocumentRoot "/Applications/XAMPP/xamppfiles/htdocs/webAsistenciaUCA/public"
   ErrorLog "logs/cedin.com.py.local.log"
   CustomLog "logs/cedin.com.py.local-access.log" common
   <Directory "/Applications/XAMPP/xamppfiles/htdocs/webTicketing/public">
       Options Indexes MultiViews FollowSymLinks
       DirectoryIndex index.php
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>
</VirtualHost>
------------------------------------------------------------------------------------------------------


*Configurar el HOST en las máquinas
En Mac es /etc/hosts, hay que agregar la url:
------------------------------------------------------------------------------------------------------
127.0.0.1       localhost       local.uca.edu.py
------------------------------------------------------------------------------------------------------


*Por último, atender que en el proyecto este esté incluida la librería de Zend
